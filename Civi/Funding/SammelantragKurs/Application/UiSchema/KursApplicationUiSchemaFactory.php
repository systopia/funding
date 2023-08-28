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

namespace Civi\Funding\SammelantragKurs\Application\UiSchema;

use Civi\Funding\Entity\ApplicationProcessEntityBundle;
use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\Entity\FundingCaseTypeEntity;
use Civi\Funding\Entity\FundingProgramEntity;
use Civi\Funding\Form\JsonSchema\JsonFormsSubmitButtonsFactory;
use Civi\Funding\Form\CombinedApplicationUiSchemaFactoryInterface;
use Civi\Funding\SammelantragKurs\Application\Actions\KursApplicationSubmitActionsFactory;
use Civi\Funding\SammelantragKurs\Traits\KursSupportedFundingCaseTypesTrait;
use Civi\RemoteTools\JsonForms\JsonFormsElement;

final class KursApplicationUiSchemaFactory implements CombinedApplicationUiSchemaFactoryInterface {

  use KursSupportedFundingCaseTypesTrait;

  private KursApplicationSubmitActionsFactory $submitActionsFactory;

  public function __construct(KursApplicationSubmitActionsFactory $submitActionsFactory) {
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

    $uiSchema = new KursApplicationUiSchema(
      $applicationProcess->getIdentifier(),
      $fundingProgram->getCurrency(),
      $submitButtons,
    );

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
  public function createUiSchemaAdd(
    FundingProgramEntity $fundingProgram,
    FundingCaseTypeEntity $fundingCaseType,
    FundingCaseEntity $fundingCase
  ): JsonFormsElement {
    $submitButtons = JsonFormsSubmitButtonsFactory::createButtons(
      $this->submitActionsFactory->createInitialSubmitActions($fundingProgram->getPermissions())
    );

    return new KursApplicationUiSchema('Neuer Kurs', $fundingProgram->getCurrency(), $submitButtons);
  }

}
