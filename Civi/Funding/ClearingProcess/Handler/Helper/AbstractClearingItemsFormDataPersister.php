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
use Civi\Funding\ClearingProcess\ClearingExternalFileManager;
use Civi\Funding\ClearingProcess\ClearingResourcesItemManager;
use Civi\Funding\Entity\AbstractClearingItemEntity;
use Civi\Funding\Entity\AbstractFinancePlanItemEntity;
use Civi\Funding\Entity\ClearingCostItemEntity;
use Civi\Funding\Entity\ClearingProcessEntityBundle;
use Civi\Funding\Entity\ClearingResourcesItemEntity;
use Webmozart\Assert\Assert;

/**
 * @template TClearingItem of AbstractClearingItemEntity
 * @template TFinancePlanItem of AbstractFinancePlanItemEntity
 *
 * @phpstan-type clearingItemRecordT array{
 *   _id: int|null,
 *   amount: float,
 *   file: string|null,
 *   description: string,
 * }
 */
abstract class AbstractClearingItemsFormDataPersister {

  /**
   * @phpstan-var \Civi\Funding\ClearingProcess\AbstractClearingItemManager<TClearingItem>
   */
  private AbstractClearingItemManager $clearingItemManager;

  private ClearingExternalFileManager $externalFileManager;

  /**
   * @phpstan-var AbstractFinancePlanItemManager<TFinancePlanItem>
   */
  private AbstractFinancePlanItemManager $financePlanItemManager;

  /**
   * @phpstan-var class-string<TClearingItem>
   */
  private string $clearingItemEntityClass;

  private string $financePlanItemIdFieldName;

  private string $externalFileIdentifierPrefix;

  /**
   * @phpstan-param AbstractClearingItemManager<TClearingItem> $clearingItemManager
   * @phpstan-param AbstractFinancePlanItemManager<TFinancePlanItem> $financePlanItemManager
   */
  public function __construct(
    AbstractClearingItemManager $clearingItemManager,
    ClearingExternalFileManager $externalFileManager,
    AbstractFinancePlanItemManager $financePlanItemManager
  ) {
    $this->clearingItemManager = $clearingItemManager;
    $this->externalFileManager = $externalFileManager;
    $this->financePlanItemManager = $financePlanItemManager;

    if ($clearingItemManager instanceof ClearingCostItemManager) {
      // @phpstan-ignore-next-line
      $this->clearingItemEntityClass = ClearingCostItemEntity::class;
      $this->financePlanItemIdFieldName = 'application_cost_item_id';
      $this->externalFileIdentifierPrefix = 'costItem';
    }
    elseif ($clearingItemManager instanceof ClearingResourcesItemManager) {
      // @phpstan-ignore-next-line
      $this->clearingItemEntityClass = ClearingResourcesItemEntity::class;
      $this->financePlanItemIdFieldName = 'application_resources_item_id';
      $this->externalFileIdentifierPrefix = 'resourcesItem';
    }
    else {
      throw new \InvalidArgumentException(
        sprintf('Unexpected clearing item manager class "%s"', get_class($clearingItemManager))
      );
    }
  }

  /**
   * @phpstan-param array<int, list<clearingItemRecordT>> $clearingItemsFormData
   *   Key is the corresponding finance plan item ID.
   *
   * @phpstan-return array<string, string>
   *   Mapping of submitted file URIs to CiviCRM file URIs.
   *
   * @throws \CRM_Core_Exception
   */
  public function persistCostItems(
    ClearingProcessEntityBundle $clearingProcessBundle,
    array $clearingItemsFormData
  ): array {
    $clearingProcessId = $clearingProcessBundle->getClearingProcess()->getId();
    $clearingItems = [];
    $files = [];
    foreach ($clearingItemsFormData as $financePlanItemId => $records) {
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

      foreach ($records as $record) {
        [$clearingItem, $externalFile] = $this->createClearingItem($clearingProcessId, $financePlanItem, $record);
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
    int $clearingProcessId,
    AbstractFinancePlanItemEntity $financePlanItem,
    array $record
  ): array {
    if (isset($record['file'])) {
      $externalFile = $this->externalFileManager->addOrUpdateFile(
        $record['file'],
        $this->getFileIdentifier($financePlanItem),
        $clearingProcessId
      );
      $fileId = $externalFile->getFileId();
    }
    else {
      $externalFile = NULL;
      $fileId = NULL;
    }

    if (isset($record['_id'])) {
      $existingClearingItem = $this->clearingItemManager->get($record['_id']);
      if (NULL !== $existingClearingItem) {
        Assert::same($existingClearingItem->get($this->financePlanItemIdFieldName), $financePlanItem->getId());

        if ($this->isClearingItemChanged($existingClearingItem, $record, $fileId)) {
          $existingClearingItem
            ->setFileId($fileId)
            ->setAmount($record['amount'])
            ->setDescription($record['description'])
            ->setStatus('new')
            ->setAmountAdmitted(NULL);
        }

        return [$existingClearingItem, $externalFile];
      }
    }

    $clearingItem = $this->clearingItemEntityClass::fromArray([
      'clearing_process_id' => $clearingProcessId,
      $this->financePlanItemIdFieldName => $financePlanItem->getId(),
      'status' => 'new',
      'file_id' => $fileId,
      'amount' => $record['amount'],
      'amount_admitted' => NULL,
      'description' => $record['description'],
    ]);

    return [$clearingItem, $externalFile];
  }

  private function getFileIdentifier(AbstractFinancePlanItemEntity $financePlanItem): string {
    return $this->externalFileIdentifierPrefix . '/' . $financePlanItem->getId();
  }

  /**
   * @phpstan-param TClearingItem $clearingItem
   * @phpstan-param clearingItemRecordT $record
   */
  private function isClearingItemChanged(AbstractClearingItemEntity $clearingItem, array $record, ?int $fileId): bool {
    return $clearingItem->getFileId() !== $fileId
      || $clearingItem->getAmount() !== $record['amount']
      || $clearingItem->getDescription() !== $record['description'];
  }

}
