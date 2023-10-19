<?php
/*
 * Copyright (C) 2023 SYSTOPIA GmbH
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

namespace Civi\Funding\IJB\Application\UiSchema;

use Civi\Funding\Entity\ApplicationProcessEntityBundle;
use Civi\Funding\Entity\FundingCaseTypeEntity;
use Civi\Funding\Entity\FundingProgramEntity;
use Civi\Funding\Form\Application\NonCombinedApplicationUiSchemaFactoryInterface;
use Civi\Funding\Form\JsonSchema\JsonFormsSubmitButtonsFactory;
use Civi\Funding\IJB\Application\Actions\IJBApplicationSubmitActionsFactory;
use Civi\Funding\IJB\Traits\IJBSupportedFundingCaseTypesTrait;
use Civi\RemoteTools\JsonForms\JsonFormsElement;

final class IJBApplicationUiSchemaFactory implements NonCombinedApplicationUiSchemaFactoryInterface {

  use IJBSupportedFundingCaseTypesTrait;

  private IJBApplicationSubmitActionsFactory $submitActionsFactory;

  public function __construct(IJBApplicationSubmitActionsFactory $submitActionsFactory) {
    $this->submitActionsFactory = $submitActionsFactory;
  }

  /**
   * @inheritDoc
   */
  public function createUiSchemaExisting(
    ApplicationProcessEntityBundle $applicationProcessBundle,
    array $applicationProcessStatusList
  ): JsonFormsElement {
    $applicationProcess = $applicationProcessBundle->getApplicationProcess();
    $fundingCase = $applicationProcessBundle->getFundingCase();
    $fundingProgram = $applicationProcessBundle->getFundingProgram();

    $submitButtons = JsonFormsSubmitButtonsFactory::createButtons(
      $this->submitActionsFactory->createSubmitActions(
        $applicationProcess->getFullStatus(),
        $applicationProcessStatusList,
        $fundingCase->getPermissions()
      ),
    );

    $uiSchema = new IJBApplicationUiSchema($fundingProgram->getCurrency(), $submitButtons);

    if (!$this->submitActionsFactory->isEditAllowed(
      $applicationProcess->getFullStatus(),
      $applicationProcessStatusList,
      $fundingCase->getPermissions()
    )) {
      $uiSchema->setReadonly(TRUE);
    }

    return $uiSchema;
  }

  /**
   * @inheritDoc
   */
  public function createUiSchemaNew(
    FundingProgramEntity $fundingProgram,
    FundingCaseTypeEntity $fundingCaseType
  ): JsonFormsElement {
    $submitButtons = JsonFormsSubmitButtonsFactory::createButtons(
      $this->submitActionsFactory->createInitialSubmitActions($fundingProgram->getPermissions()),
    );

    return new IJBApplicationUiSchema($fundingProgram->getCurrency(), $submitButtons);
  }

}
