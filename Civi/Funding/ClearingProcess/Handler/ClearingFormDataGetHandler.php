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

namespace Civi\Funding\ClearingProcess\Handler;

use Civi\Funding\ClearingProcess\ClearingCostItemManager;
use Civi\Funding\ClearingProcess\ClearingExternalFileManagerInterface;
use Civi\Funding\ClearingProcess\ClearingResourcesItemManager;
use Civi\Funding\ClearingProcess\Command\ClearingFormDataGetCommand;
use Civi\Funding\ClearingProcess\Command\ClearingFormValidateCommand;
use Civi\Funding\ClearingProcess\Form\ReportDataLoaderInterface;
use Civi\Funding\Entity\AbstractClearingItemEntity;
use Civi\Funding\Entity\ClearingCostItemEntity;
use Civi\Funding\Entity\ClearingResourcesItemEntity;

/**
 * @phpstan-import-type clearingItemsT from \Civi\Funding\ClearingProcess\Form\ClearingFormGenerator
 */
final class ClearingFormDataGetHandler implements ClearingFormDataGetHandlerInterface {

  private ClearingCostItemManager $clearingCostItemManager;

  private ClearingResourcesItemManager $clearingResourcesItemManager;

  private ClearingExternalFileManagerInterface $externalFileManager;

  private ReportDataLoaderInterface $reportDataLoader;

  private ClearingFormValidateHandlerInterface $validateHandler;

  public function __construct(
    ClearingCostItemManager $clearingCostItemManager,
    ClearingResourcesItemManager $clearingResourcesItemManager,
    ClearingExternalFileManagerInterface $externalFileManager,
    ReportDataLoaderInterface $reportDataLoader,
    ClearingFormValidateHandlerInterface $validateHandler
  ) {
    $this->clearingCostItemManager = $clearingCostItemManager;
    $this->clearingResourcesItemManager = $clearingResourcesItemManager;
    $this->externalFileManager = $externalFileManager;
    $this->reportDataLoader = $reportDataLoader;
    $this->validateHandler = $validateHandler;
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function handle(ClearingFormDataGetCommand $command): array {
    $clearingProcessId = $command->getClearingProcess()->getId();

    $data = [
      'reportData' => $this->reportDataLoader->getReportData($command->getClearingProcessBundle()),
      'costItems' => [],
      'resourcesItems' => [],
    ];

    foreach ($this->clearingCostItemManager->getByClearingProcessId($clearingProcessId) as $clearingItem) {
      $costItemRecord = [
        '_id' => $clearingItem->getId(),
        '_financePlanItemId' => $clearingItem->getFinancePlanItemId(),
        'amount' => $clearingItem->getAmount(),
        'file' => $this->getExternalFileUri($clearingItem),
        'receiptNumber' => $clearingItem->getReceiptNumber(),
        'receiptDate' => $clearingItem->getReceiptDate()?->format('Y-m-d'),
        'paymentDate' => $clearingItem->getPaymentDate()?->format('Y-m-d'),
        'paymentParty' => $clearingItem->getPaymentParty(),
        'reason' => $clearingItem->getReason(),
        'properties' => $clearingItem->getProperties(),
        'amountAdmitted' => $clearingItem->getAmountAdmitted(),
      ];
      // @phpstan-ignore argument.type
      [$dataKey, $recordKey] = $this->getDataKeyAndRecordKey($clearingItem, $data['costItems']);
      $data['costItems'][$dataKey]['records'][$recordKey] = $costItemRecord;
    }

    foreach ($this->clearingResourcesItemManager->getByClearingProcessId($clearingProcessId) as $clearingItem) {
      $resourcesItemData = [
        '_id' => $clearingItem->getId(),
        '_financePlanItemId' => $clearingItem->getFinancePlanItemId(),
        'amount' => $clearingItem->getAmount(),
        'file' => $this->getExternalFileUri($clearingItem),
        'receiptNumber' => $clearingItem->getReceiptNumber(),
        'receiptDate' => $clearingItem->getReceiptDate()?->format('Y-m-d'),
        'paymentDate' => $clearingItem->getPaymentDate()?->format('Y-m-d'),
        'paymentParty' => $clearingItem->getPaymentParty(),
        'reason' => $clearingItem->getReason(),
        'properties' => $clearingItem->getProperties(),
        'amountAdmitted' => $clearingItem->getAmountAdmitted(),
      ];
      // @phpstan-ignore argument.type
      [$dataKey, $recordKey] = $this->getDataKeyAndRecordKey($clearingItem, $data['resourcesItems']);
      $data['resourcesItems'][$dataKey]['records'][$recordKey] = $resourcesItemData;
    }

    // Perform calculations.
    $result = $this->validateHandler->handle(
      new ClearingFormValidateCommand($command->getClearingProcessBundle(), $data, 10)
    );

    return $result->getData();
  }

  /**
   * @throws \CRM_Core_Exception
   *
   * @phpstan-ignore-next-line Generic of $clearingItem not specified.
   */
  private function getExternalFileUri(AbstractClearingItemEntity $clearingItem): ?string {
    $externalFile = $this->externalFileManager->getFile($clearingItem);

    return $externalFile?->getUri();
  }

  /**
   * @phpstan-param clearingItemsT $clearingItemsData
   *
   * @phpstan-return array{int|string, int|string}
   */
  private function getDataKeyAndRecordKey(
    ClearingCostItemEntity|ClearingResourcesItemEntity $clearingItem,
    array $clearingItemsData
  ): array {
    [$dataKey, $recordKey] = explode('/', $clearingItem->getFormKey());

    if (1 === preg_match('/^\d+$/', $recordKey)) {
      // By not using the given number it is possible to remove a persisted
      // clearing item and the resulting records array will still be an array
      // when JSON serialized.
      $recordKey = count($clearingItemsData[$dataKey]['records'] ?? []);
    }

    return [$dataKey, $recordKey];
  }

}
