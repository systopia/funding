<?php
/*
 * Copyright (C) 2024 SYSTOPIA GmbH
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as published by
 *  the Free Software Foundation in version 3.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types = 1);

namespace Civi\Funding\ClearingProcess\Handler\Helper;

use Civi\Funding\ApplicationProcess\AbstractFinancePlanItemManager;
use Civi\Funding\ClearingProcess\AbstractClearingItemManager;
use Civi\Funding\ClearingProcess\ClearingCostItemManager;
use Civi\Funding\ClearingProcess\ClearingExternalFileManagerInterface;
use Civi\Funding\ClearingProcess\ClearingResourcesItemManager;
use Civi\Funding\ClearingProcess\Traits\HasClearingReviewPermissionTrait;
use Civi\Funding\Entity\AbstractClearingItemEntity;
use Civi\Funding\Entity\AbstractFinancePlanItemEntity;
use Civi\Funding\Entity\ClearingCostItemEntity;
use Civi\Funding\Entity\ClearingProcessEntityBundle;
use Civi\Funding\Entity\ClearingResourcesItemEntity;
use Civi\Funding\Entity\ExternalFileEntity;
use Civi\Funding\Exception\FundingException;
use Civi\Funding\Util\DateTimeUtil;
use Webmozart\Assert\Assert;

/**
 * @template TClearingItem of AbstractClearingItemEntity
 * @template TFinancePlanItem of AbstractFinancePlanItemEntity
 *
 * @phpstan-import-type clearingItemRecordT from \Civi\Funding\ClearingProcess\Form\ClearingFormGenerator
 */
abstract class AbstractClearingItemsFormDataPersister {

  use HasClearingReviewPermissionTrait;

  /**
   * @phpstan-var \Civi\Funding\ClearingProcess\AbstractClearingItemManager<TClearingItem>
   */
  private AbstractClearingItemManager $clearingItemManager;

  private ClearingExternalFileManagerInterface $externalFileManager;

  /**
   * @phpstan-var AbstractFinancePlanItemManager<TFinancePlanItem>
   */
  private AbstractFinancePlanItemManager $financePlanItemManager;

  /**
   * @phpstan-var class-string<TClearingItem>
   */
  private string $clearingItemEntityClass;

  private string $financePlanItemIdFieldName;

  /**
   * @phpstan-param AbstractClearingItemManager<TClearingItem> $clearingItemManager
   * @phpstan-param AbstractFinancePlanItemManager<TFinancePlanItem> $financePlanItemManager
   */
  public function __construct(
    AbstractClearingItemManager $clearingItemManager,
    ClearingExternalFileManagerInterface $externalFileManager,
    AbstractFinancePlanItemManager $financePlanItemManager
  ) {
    $this->clearingItemManager = $clearingItemManager;
    $this->externalFileManager = $externalFileManager;
    $this->financePlanItemManager = $financePlanItemManager;

    if ($clearingItemManager instanceof ClearingCostItemManager) {
      // @phpstan-ignore-next-line
      $this->clearingItemEntityClass = ClearingCostItemEntity::class;
      $this->financePlanItemIdFieldName = 'application_cost_item_id';
    }
    elseif ($clearingItemManager instanceof ClearingResourcesItemManager) {
      // @phpstan-ignore-next-line
      $this->clearingItemEntityClass = ClearingResourcesItemEntity::class;
      $this->financePlanItemIdFieldName = 'app_resources_item_id';
    }
    else {
      throw new \InvalidArgumentException(
        sprintf('Unexpected clearing item manager class "%s"', get_class($clearingItemManager))
      );
    }
  }

  /**
   * @phpstan-param array<int, array{records: list<clearingItemRecordT>}> $clearingItemsFormData
   *   Key is the corresponding finance plan item ID.
   *
   * @phpstan-return array<string, string>
   *   Mapping of submitted file URIs to CiviCRM file URIs.
   *
   * @throws \CRM_Core_Exception
   */
  public function persistClearingItems(
    ClearingProcessEntityBundle $clearingProcessBundle,
    array $clearingItemsFormData
  ): array {
    $clearingProcessId = $clearingProcessBundle->getClearingProcess()->getId();
    $clearingItems = [];
    $files = [];
    foreach ($clearingItemsFormData as $financePlanItemId => $data) {
      $financePlanItem = $this->financePlanItemManager->get($financePlanItemId);
      Assert::notNull($financePlanItem, sprintf('Invalid finance plan item ID %d', $financePlanItemId));

      $applicationProcessId = $clearingProcessBundle->getApplicationProcess()->getId();
      Assert::same(
        $applicationProcessId,
        $financePlanItem->getApplicationProcessId(),
        sprintf(
          'Expected application process ID of finance plan item with ID %d to be %d',
          $financePlanItemId,
          $applicationProcessId
        )
      );

      foreach ($data['records'] as $record) {
        [$clearingItem, $externalFile] = $this->createClearingItem($clearingProcessBundle, $financePlanItem, $record);
        $clearingItems[] = $clearingItem;
        if (NULL !== $externalFile) {
          $files[$record['file']] = $externalFile->getUri();
        }
      }
    }

    $this->clearingItemManager->updateAll($clearingProcessId, $clearingItems);

    return $files;
  }

  /**
   * @phpstan-param TFinancePlanItem $financePlanItem
   * @phpstan-param clearingItemRecordT $record
   *
   * @phpstan-return array{TClearingItem, ?\Civi\Funding\Entity\ExternalFileEntity}
   *
   * @throws \CRM_Core_Exception
   */
  private function createClearingItem(
    ClearingProcessEntityBundle $clearingProcessBundle,
    AbstractFinancePlanItemEntity $financePlanItem,
    array $record
  ): array {
    $clearingProcessId = $clearingProcessBundle->getClearingProcess()->getId();
    $permissions = $clearingProcessBundle->getFundingCase()->getPermissions();

    if (isset($record['_id'])) {
      $existingClearingItem = $this->clearingItemManager->get($record['_id']);
      if (NULL === $existingClearingItem) {
        // Clearing item has been removed by another request. The record may
        // contain a link to a file already loaded into CiviCRM that was deleted
        // when the clearing item has been removed.
        throw new FundingException('Clearing was modified after loading the form');
      }

      Assert::same($existingClearingItem->get($this->financePlanItemIdFieldName), $financePlanItem->getId());
      $externalFile = $this->handleFile($record, $existingClearingItem, $clearingProcessId);
      $fileId = NULL === $externalFile ? NULL : $externalFile->getFileId();

      $status = $this->determineStatus($record, $existingClearingItem, $fileId, $permissions);
      $existingClearingItem
        ->setFileId($fileId)
        ->setReceiptNumber($record['receiptNumber'])
        ->setReceiptDate(DateTimeUtil::toDateTimeOrNull($record['receiptDate']))
        ->setPaymentDate(new \DateTime($record['paymentDate']))
        ->setRecipient($record['recipient'])
        ->setReason($record['reason'])
        ->setAmount($record['amount'])
        ->setStatus($status);
      if ($this->hasReviewPermission($permissions)) {
        $existingClearingItem->setAmountAdmitted($record['amountAdmitted']);
      }
      elseif ('new' === $status) {
        $existingClearingItem->setAmountAdmitted(NULL);
      }

      return [$existingClearingItem, $externalFile];
    }

    $externalFile = $this->handleFile($record, NULL, $clearingProcessId);
    $fileId = NULL === $externalFile ? NULL : $externalFile->getFileId();
    $clearingItem = $this->clearingItemEntityClass::fromArray([
      'clearing_process_id' => $clearingProcessId,
      $this->financePlanItemIdFieldName => $financePlanItem->getId(),
      'status' => $this->determineStatus($record, NULL, $fileId, $permissions),
      'file_id' => $fileId,
      'receipt_number' => $record['receiptNumber'],
      'receipt_date' => $record['receiptDate'],
      'payment_date' => $record['paymentDate'],
      'recipient' => $record['recipient'],
      'reason' => $record['reason'],
      'amount' => $record['amount'],
      'amount_admitted' => NULL,
    ]);

    if ($this->hasReviewPermission($permissions)) {
      $clearingItem->setAmountAdmitted($record['amountAdmitted']);
    }

    return [$clearingItem, $externalFile];
  }

  /**
   * @phpstan-param clearingItemRecordT $record
   * @phpstan-param TClearingItem $existingClearingItem
   * @phpstan-param list<string> $permissions
   */
  private function determineStatus(
    array $record,
    ?AbstractClearingItemEntity $existingClearingItem,
    ?int $fileId,
    array $permissions
  ): string {
    if ($this->hasReviewPermission($permissions)) {
      if (NULL === $record['amountAdmitted']) {
        return 'new';
      }

      return $record['amountAdmitted'] > 0 ? 'accepted' : 'rejected';
    }

    if (NULL === $existingClearingItem) {
      return 'new';
    }

    return $this->isClearingItemChanged($existingClearingItem, $record, $fileId)
      ? 'new' : $existingClearingItem->getStatus();
  }

  /**
   * @phpstan-param clearingItemRecordT $record
   * @phpstan-param TClearingItem $existingClearingItem
   *
   * @throws \CRM_Core_Exception
   */
  private function handleFile(
    array $record,
    ?AbstractClearingItemEntity $existingClearingItem,
    int $clearingProcessId
  ): ?ExternalFileEntity {
    if (!isset($record['file'])) {
      return NULL;
    }

    return $this->externalFileManager->addOrUpdateFile($record['file'], $existingClearingItem, $clearingProcessId);
  }

  /**
   * @phpstan-param TClearingItem $clearingItem
   * @phpstan-param clearingItemRecordT $record
   */
  private function isClearingItemChanged(AbstractClearingItemEntity $clearingItem, array $record, ?int $fileId): bool {
    return $clearingItem->getFileId() !== $fileId
      || $clearingItem->getReceiptNumber() !== $record['receiptNumber']
      || $clearingItem->getReceiptDate()?->format('Y-m-d') !== $record['receiptDate']
      || $clearingItem->getPaymentDate()->format('Y-m-d') !== $record['paymentDate']
      || $clearingItem->getRecipient() !== $record['recipient']
      || $clearingItem->getReason() !== $record['reason']
      || abs($clearingItem->getAmount() - $record['amount']) >= PHP_FLOAT_EPSILON;
  }

}
