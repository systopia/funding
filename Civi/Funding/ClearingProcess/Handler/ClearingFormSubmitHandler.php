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

use Civi\Api4\FundingClearingProcess;
use Civi\Funding\ClearingProcess\ClearingActionsDeterminer;
use Civi\Funding\ClearingProcess\Command\ClearingActionApplyCommand;
use Civi\Funding\ClearingProcess\Command\ClearingFormDataGetCommand;
use Civi\Funding\ClearingProcess\Command\ClearingFormSubmitCommand;
use Civi\Funding\ClearingProcess\Command\ClearingFormSubmitResult;
use Civi\Funding\ClearingProcess\Command\ClearingFormValidateCommand;
use Civi\Funding\ClearingProcess\Handler\Helper\AbstractClearingItemsFormDataPersister;
use Civi\Funding\ClearingProcess\Handler\Helper\ClearingCommentPersister;
use Civi\Funding\ClearingProcess\Handler\Helper\ClearingCostItemsFormDataPersister;
use Civi\Funding\ClearingProcess\Handler\Helper\ClearingResourcesItemsFormDataPersister;
use Civi\Funding\ExternalFile\TaggedExternalFilePersister;

/**
 * @phpstan-import-type clearingFormDataT from \Civi\Funding\ClearingProcess\Form\ClearingFormGenerator
 */
final class ClearingFormSubmitHandler implements ClearingFormSubmitHandlerInterface {

  private ClearingActionApplyHandlerInterface $actionApplyHandler;

  private ClearingActionsDeterminer $actionsDeterminer;

  private ClearingCostItemsFormDataPersister $clearingCostItemsFormDataPersister;

  private ClearingResourcesItemsFormDataPersister $clearingResourcesItemsFormDataPersister;

  private ClearingCommentPersister $commentPersister;

  private TaggedExternalFilePersister $externalFilePersister;

  private ClearingFormDataGetHandlerInterface $formDataGetHandler;

  private ClearingFormValidateHandlerInterface $validateHandler;

  public function __construct(
    ClearingActionApplyHandlerInterface $actionApplyHandler,
    ClearingActionsDeterminer $actionsDeterminer,
    ClearingCostItemsFormDataPersister $clearingCostItemsFormDataPersister,
    ClearingResourcesItemsFormDataPersister $clearingResourcesItemsFormDataPersister,
    ClearingCommentPersister $commentPersister,
    TaggedExternalFilePersister $externalFilePersister,
    ClearingFormDataGetHandlerInterface $formDataGetHandler,
    ClearingFormValidateHandlerInterface $validateHandler
  ) {
    $this->actionApplyHandler = $actionApplyHandler;
    $this->actionsDeterminer = $actionsDeterminer;
    $this->clearingCostItemsFormDataPersister = $clearingCostItemsFormDataPersister;
    $this->clearingResourcesItemsFormDataPersister = $clearingResourcesItemsFormDataPersister;
    $this->commentPersister = $commentPersister;
    $this->externalFilePersister = $externalFilePersister;
    $this->formDataGetHandler = $formDataGetHandler;
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
        $validationResult->getErrorMessages(), $validationResult->getData(), []
      );
    }

    /** @phpstan-var clearingFormDataT $data */
    $data = $validationResult->getData();
    $files = [];
    $clearingProcessBundle = $command->getClearingProcessBundle();
    $clearingProcess = $clearingProcessBundle->getClearingProcess();
    $contentChangeAllowed = $this->actionsDeterminer->isContentChangeAllowed($clearingProcessBundle);

    if ($this->actionsDeterminer->isEditAction($data['_action'])) {
      $files += $this->clearingCostItemsFormDataPersister->persistClearingItems(
        $clearingProcessBundle,
        $data['costItems'] ?? [],
        $contentChangeAllowed ? AbstractClearingItemsFormDataPersister::FLAG_CONTENT_CHANGE_ALLOWED : 0
      );
      $files += $this->clearingResourcesItemsFormDataPersister->persistClearingItems(
        $clearingProcessBundle,
        $data['resourcesItems'] ?? [],
        $contentChangeAllowed ? AbstractClearingItemsFormDataPersister::FLAG_CONTENT_CHANGE_ALLOWED : 0
      );

      if ($contentChangeAllowed) {
        $files += $this->externalFilePersister->handleFiles(
          $validationResult->getTaggedData(),
          $data,
          FundingClearingProcess::getEntityName(),
          $clearingProcess->getId()
        );

        $clearingProcess->setReportData($data['reportData'] ?? []);
      }
    }

    $this->actionApplyHandler->handle(
      new ClearingActionApplyCommand($command->getClearingProcessBundle(), $data['_action'])
    );

    if (isset($data['comment']) && '' !== $data['comment']['text']) {
      $this->commentPersister->persistComment(
        $clearingProcessBundle,
        $data['comment']['type'],
        $data['comment']['text'],
        $data['_action']
      );
    }

    $data = $this->formDataGetHandler->handle(new ClearingFormDataGetCommand($clearingProcessBundle));

    return new ClearingFormSubmitResult([], $data, $files);
  }

}
