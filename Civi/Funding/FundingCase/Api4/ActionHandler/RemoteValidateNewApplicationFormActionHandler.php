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

use Civi\Funding\Api4\Action\Remote\FundingCase\ValidateNewApplicationFormAction;
use Civi\Funding\ApplicationProcess\Command\ApplicationFormNewValidateCommand;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormNewValidateHandlerInterface;
use Civi\Funding\FundingCase\Api4\ActionHandler\Traits\NewApplicationFormRemoteActionHandlerTrait;
use Civi\Funding\FundingProgram\FundingCaseTypeManager;
use Civi\Funding\FundingProgram\FundingCaseTypeProgramRelationChecker;
use Civi\Funding\FundingProgram\FundingProgramManager;
use Civi\RemoteTools\ActionHandler\ActionHandlerInterface;
use Webmozart\Assert\Assert;

final class RemoteValidateNewApplicationFormActionHandler implements ActionHandlerInterface {

  use NewApplicationFormRemoteActionHandlerTrait;

  public const ENTITY_NAME = 'RemoteFundingCase';

  private FundingCaseTypeManager $fundingCaseTypeManager;

  private FundingProgramManager $fundingProgramManager;

  private ApplicationFormNewValidateHandlerInterface $newValidateHandler;

  public function __construct(
    FundingCaseTypeManager $fundingCaseTypeManager,
    FundingProgramManager $fundingProgramManager,
    ApplicationFormNewValidateHandlerInterface $newValidateHandler,
    FundingCaseTypeProgramRelationChecker $relationChecker
  ) {
    $this->fundingCaseTypeManager = $fundingCaseTypeManager;
    $this->fundingProgramManager = $fundingProgramManager;
    $this->newValidateHandler = $newValidateHandler;
    $this->relationChecker = $relationChecker;
  }

  /**
   * @phpstan-return array{
   *   valid: bool,
   *   errors: array<string, non-empty-list<string>>|\stdClass,
   * }
   *
   * @throws \CRM_Core_Exception
   */
  public function validateNewApplicationForm(ValidateNewApplicationFormAction $action): array {
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

    $command = new ApplicationFormNewValidateCommand(
      $fundingProgram,
      $fundingCaseType,
      $action->getData()
    );

    $result = $this->newValidateHandler->handle($command);

    return [
      'valid' => $result->isValid(),
      'errors' => [] === $result->getErrorMessages() ? new \stdClass() : $result->getErrorMessages(),
    ];
  }

}
