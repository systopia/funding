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
use Civi\Funding\ApplicationProcess\Command\ApplicationFormNewValidateCommand;
use Civi\Funding\ApplicationProcess\Form\Validation\ApplicationFormValidationResult;
use Civi\Funding\ApplicationProcess\StatusDeterminer\ApplicationProcessStatusDeterminerInterface;
use Civi\Funding\Entity\ApplicationProcessEntityBundle;
use Civi\Funding\FundingCase\FundingCaseManager;
use Webmozart\Assert\Assert;

final class ApplicationFormNewSubmitHandler implements ApplicationFormNewSubmitHandlerInterface {

  private ApplicationProcessManager $applicationProcessManager;

  private FundingCaseManager $fundingCaseManager;

  private ApplicationProcessStatusDeterminerInterface $statusDeterminer;

  private ApplicationFormNewValidateHandlerInterface $validateHandler;

  public function __construct(
    ApplicationProcessManager $applicationProcessManager,
    FundingCaseManager $fundingCaseManager,
    ApplicationProcessStatusDeterminerInterface $statusDeterminer,
    ApplicationFormNewValidateHandlerInterface $validateHandler
  ) {
    $this->applicationProcessManager = $applicationProcessManager;
    $this->fundingCaseManager = $fundingCaseManager;
    $this->statusDeterminer = $statusDeterminer;
    $this->validateHandler = $validateHandler;
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function handle(ApplicationFormNewSubmitCommand $command): ApplicationFormNewSubmitResult {
    $validationResult = $this->validateHandler->handle(new ApplicationFormNewValidateCommand(
      $command->getFundingProgram(),
      $command->getFundingCaseType(),
      $command->getData(),
    ));

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
    ApplicationFormValidationResult $validationResult
  ): ApplicationFormNewSubmitResult {
    $validatedData = $validationResult->getValidatedData();
    $applicationAddableStatusList = $command->getFundingCaseType()->getProperty('applicationAddableStatusList', []);
    Assert::allString($applicationAddableStatusList, '"applicationAddableStatusList" must be a list of strings');
    /** @phpstan-var list<string> $applicationAddableStatusList */

    $fundingCase = $this->fundingCaseManager->getOrCreate(
      $applicationAddableStatusList,
      $command->getContactId(),
      [
        'funding_program' => $command->getFundingProgram(),
        'funding_case_type' => $command->getFundingCaseType(),
        'recipient_contact_id' => $validatedData->getRecipientContactId(),
      ]
    );

    $applicationProcess = $this->applicationProcessManager->create(
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
