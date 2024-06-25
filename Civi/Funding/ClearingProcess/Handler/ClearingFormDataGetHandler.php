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
      'costItemsByType' => [],
      'resourcesItems' => [],
      'resourcesItemsByType' => [],
    ];

    foreach ($this->clearingCostItemManager->getByClearingProcessId($clearingProcessId) as $clearingItem) {
      $data['costItems'][$clearingItem->getApplicationCostItemId()]['records'][] = [
        '_id' => $clearingItem->getId(),
        'amount' => $clearingItem->getAmount(),
        'file' => $this->getExternalFileUri($clearingItem),
        'receiptNumber' => $clearingItem->getReceiptNumber(),
        'paymentDate' => $clearingItem->getPaymentDate()->format('Y-m-d'),
        'recipient' => $clearingItem->getRecipient(),
        'reason' => $clearingItem->getReason(),
        'amountAdmitted' => $clearingItem->getAmountAdmitted(),
      ];
    }

    foreach ($this->clearingResourcesItemManager->getByClearingProcessId($clearingProcessId) as $clearingItem) {
      $data['resourcesItems'][$clearingItem->getApplicationResourcesItemId()]['records'][] = [
        '_id' => $clearingItem->getId(),
        'amount' => $clearingItem->getAmount(),
        'file' => $this->getExternalFileUri($clearingItem),
        'receiptNumber' => $clearingItem->getReceiptNumber(),
        'paymentDate' => $clearingItem->getPaymentDate()->format('Y-m-d'),
        'recipient' => $clearingItem->getRecipient(),
        'reason' => $clearingItem->getReason(),
        'amountAdmitted' => $clearingItem->getAmountAdmitted(),
      ];
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

    return NULL === $externalFile ? NULL : $externalFile->getUri();
  }

}
