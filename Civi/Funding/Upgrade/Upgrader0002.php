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

namespace Civi\Funding\Upgrade;

use Civi\Funding\ApplicationProcess\ApplicationProcessBundleLoader;
use Civi\Funding\ApplicationProcess\Command\ApplicationCostItemsPersistCommand;
use Civi\Funding\ApplicationProcess\Command\ApplicationFormDataGetCommand;
use Civi\Funding\ApplicationProcess\Command\ApplicationFormValidateCommand;
use Civi\Funding\ApplicationProcess\Command\ApplicationResourcesItemsPersistCommand;
use Civi\Funding\ApplicationProcess\Handler\ApplicationCostItemsPersistHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormDataGetHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormValidateHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationResourcesItemsPersistHandlerInterface;
use Civi\Funding\FundingCase\FundingCasePermissionsCacheManager;

final class Upgrader0002 {

  private ApplicationProcessBundleLoader $applicationProcessBundleLoader;

  private ApplicationCostItemsPersistHandlerInterface $costItemsPersistHandler;

  private ApplicationFormDataGetHandlerInterface $formDataGetHandler;

  private ApplicationResourcesItemsPersistHandlerInterface $resourcesItemsPersistHandler;

  private ApplicationFormValidateHandlerInterface $validateHandler;

  private FundingCasePermissionsCacheManager $fundingCasePermissionsCacheManager;

  public function __construct(
    ApplicationProcessBundleLoader $applicationProcessBundleLoader,
    ApplicationCostItemsPersistHandlerInterface $costItemsPersistHandler,
    ApplicationFormDataGetHandlerInterface $formDataGetHandler,
    ApplicationResourcesItemsPersistHandlerInterface $resourcesItemsPersistHandler,
    ApplicationFormValidateHandlerInterface $validateHandler,
    FundingCasePermissionsCacheManager $fundingCasePermissionsCacheManager
  ) {
    $this->applicationProcessBundleLoader = $applicationProcessBundleLoader;
    $this->costItemsPersistHandler = $costItemsPersistHandler;
    $this->formDataGetHandler = $formDataGetHandler;
    $this->resourcesItemsPersistHandler = $resourcesItemsPersistHandler;
    $this->validateHandler = $validateHandler;
    $this->fundingCasePermissionsCacheManager = $fundingCasePermissionsCacheManager;
  }

  public function execute(\Log $log): void {
    $log->info('Clear funding case permissions cache');
    $this->fundingCasePermissionsCacheManager->clear();

    $log->info('Add data path to cost items and resources items');
    foreach ($this->applicationProcessBundleLoader->getAll() as $applicationProcessBundle) {
      $applicationProcessStatusList = $this->applicationProcessBundleLoader->getStatusList($applicationProcessBundle);
      $formData = $this->formDataGetHandler->handle(new ApplicationFormDataGetCommand(
        $applicationProcessBundle,
        $applicationProcessStatusList
      ));

      // Set status to 'review' and (temporary) give us reviewer permissions so
      // the form data validates with action 'update'.
      $applicationProcess = $applicationProcessBundle->getApplicationProcess();
      $status = $applicationProcess->getStatus();
      $applicationProcess->setStatus('review');
      $fundingCase = $applicationProcessBundle->getFundingCase();
      $fundingCase->setValues(['permissions' => ['review_calculative']] + $fundingCase->toArray());

      try {
        $validationResult = $this->validateHandler->handle(new ApplicationFormValidateCommand(
          $applicationProcessBundle,
          $applicationProcessStatusList,
          ['action' => 'update'] + $formData,
          10
        ));
      }
      finally {
        $applicationProcess->setStatus($status);
      }

      if ($validationResult->isValid()) {
        $this->costItemsPersistHandler->handle(
          new ApplicationCostItemsPersistCommand(
            $applicationProcessBundle, $validationResult->getValidatedData()->getCostItemsData()
          )
        );

        $this->resourcesItemsPersistHandler->handle(
          new ApplicationResourcesItemsPersistCommand(
            $applicationProcessBundle, $validationResult->getValidatedData()->getResourcesItemsData()
          )
        );
      }
      else {
        $log->warning(sprintf(
          'Validation failed for application "%s" (ID %d): %s',
          $applicationProcess->getIdentifier(),
          $applicationProcess->getId(),
          print_r($validationResult->getErrorMessages(), TRUE)
        ));
      }
    }
  }

}
