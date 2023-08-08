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

namespace Civi\Funding\ApplicationProcess\Handler;

use Civi\Funding\ApplicationProcess\ApplicationProcessManager;
use Civi\Funding\ApplicationProcess\Command\ApplicationFormNewSubmitCommand;
use Civi\Funding\ApplicationProcess\Command\ApplicationFormNewSubmitResult;
use Civi\Funding\ApplicationProcess\StatusDeterminer\ApplicationProcessStatusDeterminerInterface;
use Civi\Funding\Entity\ApplicationProcessEntityBundle;
use Civi\Funding\Form\ApplicationValidationResult;
use Civi\Funding\Form\NonSummaryApplicationValidatorInterface;
use Civi\Funding\FundingCase\FundingCaseManager;

final class ApplicationFormNewSubmitHandler implements ApplicationFormNewSubmitHandlerInterface {

  private ApplicationProcessManager $applicationProcessManager;

  private FundingCaseManager $fundingCaseManager;

  private ApplicationProcessStatusDeterminerInterface $statusDeterminer;

  private NonSummaryApplicationValidatorInterface $validator;

  public function __construct(
    ApplicationProcessManager $applicationProcessManager,
    FundingCaseManager $fundingCaseManager,
    ApplicationProcessStatusDeterminerInterface $statusDeterminer,
    NonSummaryApplicationValidatorInterface $validator
  ) {
    $this->applicationProcessManager = $applicationProcessManager;
    $this->fundingCaseManager = $fundingCaseManager;
    $this->statusDeterminer = $statusDeterminer;
    $this->validator = $validator;
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function handle(ApplicationFormNewSubmitCommand $command): ApplicationFormNewSubmitResult {
    $validationResult = $this->validator->validateInitial(
      $command->getContactId(),
      $command->getFundingProgram(),
      $command->getFundingCaseType(),
      $command->getData(),
    );

    if ($validationResult->isValid()) {
      return $this->handleValid($command, $validationResult);
    }
    else {
      return ApplicationFormNewSubmitResult::createError($validationResult);
    }
  }

  /**
   * @throws \CRM_Core_Exception
   */
  private function handleValid(
    ApplicationFormNewSubmitCommand $command,
    ApplicationValidationResult $validationResult
  ): ApplicationFormNewSubmitResult {
    $validatedData = $validationResult->getValidatedData();
    $fundingCase = $this->fundingCaseManager->getOpenOrCreate($command->getContactId(), [
      'funding_program' => $command->getFundingProgram(),
      'funding_case_type' => $command->getFundingCaseType(),
      'recipient_contact_id' => $validatedData->getRecipientContactId(),
    ]);

    $applicationProcess = $this->applicationProcessManager->create(
      $command->getContactId(),
      $fundingCase,
      $command->getFundingCaseType(),
      $command->getFundingProgram(),
      $this->statusDeterminer->getInitialStatus($validatedData->getAction()),
      $validatedData,
    );
    $applicationProcessBundle = new ApplicationProcessEntityBundle(
      $applicationProcess,
      $fundingCase,
      $command->getFundingCaseType(),
      $command->getFundingProgram(),
    );

    return ApplicationFormNewSubmitResult::createSuccess(
      $validationResult,
      $applicationProcessBundle,
    );
  }

}
