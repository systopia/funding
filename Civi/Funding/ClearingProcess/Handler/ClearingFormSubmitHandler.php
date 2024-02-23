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

use Civi\Funding\ClearingProcess\ClearingProcessManager;
use Civi\Funding\ClearingProcess\ClearingStatusDeterminer;
use Civi\Funding\ClearingProcess\Command\ClearingFormSubmitCommand;
use Civi\Funding\ClearingProcess\Command\ClearingFormSubmitResult;
use Civi\Funding\ClearingProcess\Command\ClearingFormValidateCommand;
use Civi\Funding\ClearingProcess\Handler\Helper\ClearingCostItemsFormDataPersister;
use Civi\Funding\ClearingProcess\Handler\Helper\ClearingResourcesItemsFormDataPersister;

/**
 * @phpstan-import-type clearingItemRecordT from \Civi\Funding\ClearingProcess\Handler\Helper\AbstractClearingItemsFormDataPersister
 */
final class ClearingFormSubmitHandler implements ClearingFormSubmitHandlerInterface {

  private ClearingCostItemsFormDataPersister $clearingCostItemsFormDataPersister;

  private ClearingResourcesItemsFormDataPersister $clearingResourcesItemsFormDataPersister;

  private ClearingProcessManager $clearingProcessManager;

  private ClearingStatusDeterminer $statusDeterminer;

  private ClearingFormValidateHandlerInterface $validateHandler;

  public function __construct(
    ClearingCostItemsFormDataPersister $clearingCostItemsFormDataPersister,
    ClearingResourcesItemsFormDataPersister $clearingResourcesItemsFormDataPersister,
    ClearingProcessManager $clearingProcessManager,
    ClearingStatusDeterminer $statusDeterminer,
    ClearingFormValidateHandlerInterface $validateHandler
  ) {
    $this->clearingCostItemsFormDataPersister = $clearingCostItemsFormDataPersister;
    $this->clearingResourcesItemsFormDataPersister = $clearingResourcesItemsFormDataPersister;
    $this->clearingProcessManager = $clearingProcessManager;
    $this->statusDeterminer = $statusDeterminer;
    $this->validateHandler = $validateHandler;
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function handle(ClearingFormSubmitCommand $command): ClearingFormSubmitResult {
    $validationResult = $this->validateHandler->handle(
      new ClearingFormValidateCommand($command->getClearingProcessBundle(), $command->getData())
    );

    if (!$validationResult->isValid()) {
      return new ClearingFormSubmitResult(
        $validationResult->getData(),
        $validationResult->getLeafErrorMessages(),
        []
      );
    }

    /**
     * @phpstan-var array{
     *   _action: string,
     *   costItems?: array<int, list<clearingItemRecordT>>,
     *   resourcesItems?: array<int, list<clearingItemRecordT>>,
     *   reportData?: array<string, mixed>,
     * } $data
     */
    $data = $validationResult->getData();

    $clearingProcessBundle = $command->getClearingProcessBundle();

    $files = $this->clearingCostItemsFormDataPersister->persistCostItems(
      $clearingProcessBundle,
      $data['costItems'] ?? []
    );
    $files += $this->clearingResourcesItemsFormDataPersister->persistCostItems(
      $clearingProcessBundle,
      $data['resourcesItems'] ?? []
    );

    $clearingProcess = $clearingProcessBundle->getClearingProcess();
    $clearingProcess->setStatus(
      $this->statusDeterminer->getStatus($clearingProcess->getStatus(),
        $data['_action'])
    );
    $clearingProcess->setReportData($data['reportData'] ?? []);
    $this->clearingProcessManager->update($clearingProcess);

    return new ClearingFormSubmitResult(
      $validationResult->getData(),
      $validationResult->getLeafErrorMessages(),
      $files
    );
  }

}
