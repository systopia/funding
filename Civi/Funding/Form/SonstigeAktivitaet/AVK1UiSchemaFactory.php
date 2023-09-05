<?php
/*
 * Copyright (C) 2022 SYSTOPIA GmbH
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

namespace Civi\Funding\Form\SonstigeAktivitaet;

use Civi\Funding\Entity\ApplicationProcessEntityBundle;
use Civi\Funding\Entity\FundingCaseTypeEntity;
use Civi\Funding\Entity\FundingProgramEntity;
use Civi\Funding\Form\ApplicationSubmitActionsFactoryInterface;
use Civi\Funding\Form\ApplicationUiSchemaFactoryInterface;
use Civi\Funding\Form\JsonSchema\JsonFormsSubmitButtonsFactory;
use Civi\Funding\Form\SonstigeAktivitaet\UISchema\AVK1UiSchema;
use Civi\RemoteTools\JsonForms\Control\JsonFormsHidden;
use Civi\RemoteTools\JsonForms\JsonFormsElement;
use Civi\RemoteTools\JsonForms\JsonFormsMarkup;

final class AVK1UiSchemaFactory implements ApplicationUiSchemaFactoryInterface {

  private ApplicationSubmitActionsFactoryInterface $submitActionsFactory;

  private AVK1StatusMarkupFactory $statusMarkupFactory;

  public static function getSupportedFundingCaseTypes(): array {
    return ['AVK1SonstigeAktivitaet'];
  }

  public function __construct(
    ApplicationSubmitActionsFactoryInterface $submitActionsFactory,
    AVK1StatusMarkupFactory $statusMarkupFactory
  ) {
    $this->submitActionsFactory = $submitActionsFactory;
    $this->statusMarkupFactory = $statusMarkupFactory;
  }

  public function createUiSchemaExisting(
    ApplicationProcessEntityBundle $applicationProcessBundle
  ): JsonFormsElement {
    $applicationProcess = $applicationProcessBundle->getApplicationProcess();
    $fundingCase = $applicationProcessBundle->getFundingCase();
    $fundingProgram = $applicationProcessBundle->getFundingProgram();

    $submitButtons = JsonFormsSubmitButtonsFactory::createButtons(
      $this->submitActionsFactory->createSubmitActions(
        $applicationProcess->getFullStatus(),
        $fundingCase->getPermissions()
      )
    );
    $hiddenFields = [new JsonFormsHidden('#/properties/applicationProcessId')];
    $statusMarkup = new JsonFormsMarkup($this->statusMarkupFactory->buildStatusMarkup($applicationProcessBundle));

    $uiSchema = new AVK1UiSchema($fundingProgram->getCurrency(), $submitButtons, $hiddenFields, [$statusMarkup]);

    if (!$this->submitActionsFactory->isEditAllowed(
      $applicationProcess->getFullStatus(),
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
    $hiddenFields = [
      new JsonFormsHidden('#/properties/fundingCaseTypeId'),
      new JsonFormsHidden('#/properties/fundingProgramId'),
    ];

    return new AVK1UiSchema($fundingProgram->getCurrency(), $submitButtons, $hiddenFields);
  }

}
