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

namespace Civi\Funding\SonstigeAktivitaet\Application\UISchema;

use Civi\Funding\Entity\ApplicationProcessEntityBundle;
use Civi\Funding\Entity\FundingCaseTypeEntity;
use Civi\Funding\Entity\FundingProgramEntity;
use Civi\Funding\Form\Application\NonCombinedApplicationUiSchemaFactoryInterface;
use Civi\Funding\Form\JsonSchema\JsonFormsSubmitButtonsFactory;
use Civi\Funding\SonstigeAktivitaet\Application\Actions\AVK1ApplicationSubmitActionsFactory;
use Civi\Funding\SonstigeAktivitaet\Application\JsonSchema\AVK1StatusMarkupFactory;
use Civi\Funding\SonstigeAktivitaet\Traits\AVK1SupportedFundingCaseTypesTrait;
use Civi\RemoteTools\JsonForms\JsonFormsElement;
use Civi\RemoteTools\JsonForms\JsonFormsMarkup;

final class AVK1UiSchemaFactory implements NonCombinedApplicationUiSchemaFactoryInterface {

  use AVK1SupportedFundingCaseTypesTrait;

  private AVK1ApplicationSubmitActionsFactory $submitActionsFactory;

  private AVK1StatusMarkupFactory $statusMarkupFactory;

  public function __construct(
    AVK1ApplicationSubmitActionsFactory $submitActionsFactory,
    AVK1StatusMarkupFactory $statusMarkupFactory
  ) {
    $this->submitActionsFactory = $submitActionsFactory;
    $this->statusMarkupFactory = $statusMarkupFactory;
  }

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
      )
    );
    $statusMarkup = new JsonFormsMarkup($this->statusMarkupFactory->buildStatusMarkup($applicationProcessBundle));

    $uiSchema = new AVK1UiSchema($fundingProgram->getCurrency(), $submitButtons, [$statusMarkup]);

    if (!$this->submitActionsFactory->isEditAllowed(
      $applicationProcess->getFullStatus(),
      $applicationProcessStatusList,
      $fundingCase->getPermissions()
    )) {
      $uiSchema->setReadonly(TRUE);
    }

    return $uiSchema;
  }

  public function createUiSchemaNew(
    FundingProgramEntity $fundingProgram,
    FundingCaseTypeEntity $fundingCaseType
  ): JsonFormsElement {
    $submitButtons = JsonFormsSubmitButtonsFactory::createButtons(
      $this->submitActionsFactory->createInitialSubmitActions($fundingProgram->getPermissions()),
    );

    return new AVK1UiSchema($fundingProgram->getCurrency(), $submitButtons);
  }

}
