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

namespace Civi\Funding\FundingCase\Api4\ActionHandler;

use Civi\Funding\Api4\Action\Remote\FundingCase\GetNewApplicationFormAction;
use Civi\Funding\ApplicationProcess\ApplicationProcessManager;
use Civi\Funding\ApplicationProcess\Command\ApplicationFormDataGetCommand;
use Civi\Funding\ApplicationProcess\Command\ApplicationFormNewCreateCommand;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormDataGetHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormNewCreateHandlerInterface;
use Civi\Funding\FundingCase\Api4\ActionHandler\Traits\NewApplicationFormRemoteActionHandlerTrait;
use Civi\Funding\FundingProgram\FundingCaseTypeManager;
use Civi\Funding\FundingProgram\FundingCaseTypeProgramRelationChecker;
use Civi\Funding\FundingProgram\FundingProgramManager;
use Civi\RemoteTools\ActionHandler\ActionHandlerInterface;
use Webmozart\Assert\Assert;
use CRM_Funding_ExtensionUtil as E;

final class RemoteGetNewApplicationFormActionHandler implements ActionHandlerInterface {

  use NewApplicationFormRemoteActionHandlerTrait;

  public const ENTITY_NAME = 'RemoteFundingCase';

  public function __construct(
    private readonly ApplicationProcessManager $applicationProcessManager,
    private readonly ApplicationFormDataGetHandlerInterface $formDataGetHandler,
    private readonly FundingCaseTypeManager $fundingCaseTypeManager,
    private readonly FundingProgramManager $fundingProgramManager,
    private readonly ApplicationFormNewCreateHandlerInterface $newCreateHandler,
    FundingCaseTypeProgramRelationChecker $relationChecker
  ) {
    $this->relationChecker = $relationChecker;
  }

  /**
   * @phpstan-return array{
   *   data: array<mixed>,
   *   jsonSchema: array<mixed>,
   *   uiSchema: array<mixed>,
   * }
   *
   * @throws \CRM_Core_Exception
   */
  public function getNewApplicationForm(GetNewApplicationFormAction $action): array {
    $this->assertFundingCaseTypeAndProgramRelated($action->getFundingCaseTypeId(), $action->getFundingProgramId());

    $fundingCaseType = $this->fundingCaseTypeManager->get($action->getFundingCaseTypeId());
    Assert::notNull(
      $fundingCaseType,
      sprintf('Funding case type wit ID "%d" not found', $action->getFundingCaseTypeId())
    );
    $fundingProgram = $this->fundingProgramManager->get($action->getFundingProgramId());
    Assert::notNull(
      $fundingProgram,
      sprintf('Funding program wit ID "%d" not found', $action->getFundingProgramId())
    );

    $this->assertCreateApplicationPermission($fundingProgram);
    $this->assertFundingProgramDates($fundingProgram);

    $form = $this->newCreateHandler->handle(
      new ApplicationFormNewCreateCommand(
        $fundingCaseType,
        $fundingProgram,
      )
    );

    if (NULL !== $action->getCopyDataFromId()) {
      $applicationProcessBundle = $this->applicationProcessManager->getBundle($action->getCopyDataFromId());
      Assert::notNull(
        $applicationProcessBundle,
        sprintf('Application process with ID "%d" not found', $action->getCopyDataFromId())
      );
      Assert::same(
        $fundingCaseType->getId(),
        $applicationProcessBundle->getFundingCaseType()->getId(),
        'Copies are only allowed with the same funding case type'
      );
      $formData = $this->formDataGetHandler->handle(new ApplicationFormDataGetCommand(
          $applicationProcessBundle,
          $this->applicationProcessManager->getStatusList($applicationProcessBundle),
          ApplicationFormDataGetCommand::FLAG_COPY
        )) + $form->getData();
    }

    return [
      'data' => $formData ?? $form->getData(),
      'jsonSchema' => $form->getJsonSchema()->toArray(),
      'uiSchema' => $form->getUiSchema()->toArray(),
    ];
  }

}
