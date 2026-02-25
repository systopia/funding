<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
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

namespace Civi\Funding\FundingCaseTypes\AuL\SammelantragKurs\FundingCase\UiSchema;

use Civi\Funding\ApplicationProcess\ApplicationProcessManager;
use Civi\Funding\Entity\ApplicationProcessEntity;
use Civi\Funding\Entity\FundingCaseBundle;
use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\Entity\FundingCaseTypeEntity;
use Civi\Funding\Entity\FundingProgramEntity;
use Civi\Funding\Form\FundingCase\FundingCaseUiSchemaFactoryInterface;
use Civi\Funding\Form\JsonSchema\JsonFormsSubmitButtonsFactory;
use Civi\Funding\FundingCase\Actions\FundingCaseSubmitActionsFactoryInterface;
use Civi\Funding\FundingCaseTypes\AuL\SammelantragKurs\Traits\KursSupportedFundingCaseTypesTrait;
use Civi\RemoteTools\JsonForms\JsonFormsElement;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsGroup;

final class KursCaseUiSchemaFactory implements FundingCaseUiSchemaFactoryInterface {

  use KursSupportedFundingCaseTypesTrait;

  private ApplicationProcessManager $applicationProcessManager;

  private FundingCaseSubmitActionsFactoryInterface $submitActionsFactory;

  public function __construct(
    ApplicationProcessManager $applicationProcessManager,
    FundingCaseSubmitActionsFactoryInterface $submitActionsFactory
  ) {
    $this->applicationProcessManager = $applicationProcessManager;
    $this->submitActionsFactory = $submitActionsFactory;
  }

  public function createUiSchemaUpdate(
    FundingProgramEntity $fundingProgram,
    FundingCaseTypeEntity $fundingCaseType,
    FundingCaseEntity $fundingCase
  ): JsonFormsElement {
    $applicationProcesses = $this->applicationProcessManager->getByFundingCaseId($fundingCase->getId());

    $submitButtons = JsonFormsSubmitButtonsFactory::createButtons(
      $this->submitActionsFactory->getSubmitActions(
        new FundingCaseBundle($fundingCase, $fundingCaseType, $fundingProgram),
        array_map(
          fn (ApplicationProcessEntity $applicationProcess) => $applicationProcess->getFullStatus(),
          $applicationProcesses
        )
      )
    );

    return new JsonFormsGroup('', $submitButtons);
  }

  public function createUiSchemaNew(
    FundingProgramEntity $fundingProgram,
    FundingCaseTypeEntity $fundingCaseType
  ): JsonFormsElement {
    $submitButtons = JsonFormsSubmitButtonsFactory::createButtons(
      $this->submitActionsFactory->getInitialSubmitActions($fundingProgram->getPermissions(), $fundingCaseType)
    );

    return new KursNewCaseUiSchema($submitButtons);
  }

}
