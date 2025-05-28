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
use Civi\Funding\FundingCaseTypes\AuL\SammelantragKurs\KursConstants;
use Civi\RemoteTools\Api4\Query\Comparison;

final class Upgrader0003 implements UpgraderInterface {

  private ApplicationProcessBundleLoader $applicationProcessBundleLoader;

  private ApplicationCostItemsPersistHandlerInterface $costItemsPersistHandler;

  private ApplicationFormDataGetHandlerInterface $formDataGetHandler;

  private ApplicationResourcesItemsPersistHandlerInterface $resourcesItemsPersistHandler;

  private ApplicationFormValidateHandlerInterface $validateHandler;

  public function __construct(
    ApplicationProcessBundleLoader $applicationProcessBundleLoader,
    ApplicationCostItemsPersistHandlerInterface $costItemsPersistHandler,
    ApplicationFormDataGetHandlerInterface $formDataGetHandler,
    ApplicationResourcesItemsPersistHandlerInterface $resourcesItemsPersistHandler,
    ApplicationFormValidateHandlerInterface $validateHandler
  ) {
    $this->applicationProcessBundleLoader = $applicationProcessBundleLoader;
    $this->costItemsPersistHandler = $costItemsPersistHandler;
    $this->formDataGetHandler = $formDataGetHandler;
    $this->resourcesItemsPersistHandler = $resourcesItemsPersistHandler;
    $this->validateHandler = $validateHandler;
  }

  /**
   * Previous Kurs application JSON schemas didn't have the property "finanzierung". This is added here with default
   * values.
   *
   * @throws \CRM_Core_Exception
   */
  public function execute(\Log $log): void {
    $log->info('Add "finanzierung" to Kurs applications, if missing');
    foreach ($this->applicationProcessBundleLoader->getBy(
      Comparison::new('funding_case_id.funding_case_type_id.name', '=', KursConstants::FUNDING_CASE_TYPE_NAME)
    ) as $applicationProcessBundle) {
      $applicationProcessStatusList = $this->applicationProcessBundleLoader->getStatusList($applicationProcessBundle);
      $formData = $this->formDataGetHandler->handle(new ApplicationFormDataGetCommand(
        $applicationProcessBundle,
        $applicationProcessStatusList
      ));

      if (isset($formData['finanzierung'])) {
        continue;
      }

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
          [
            '_action' => 'update',
            'finanzierung' => (object) [
              'teilnehmerbeitraege' => 0,
              'eigenmittel' => 0,
              'oeffentlicheMittel' => (object) [
                'europa' => 0,
                'bundeslaender' => 0,
                'staedteUndKreise' => 0,
              ],
            ],
          ] + $formData,
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
