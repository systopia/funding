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
use Civi\Funding\Form\ApplicationJsonSchemaFactoryInterface;
use Civi\Funding\Form\Validation\ValidationResult;
use Civi\Funding\Form\Validation\ValidatorInterface;
use Civi\Funding\FundingCase\FundingCaseManager;

final class ApplicationFormNewSubmitHandler implements ApplicationFormNewSubmitHandlerInterface {

  private ApplicationProcessManager $applicationProcessManager;

  private ApplicationJsonSchemaFactoryInterface $jsonSchemaFactory;

  private FundingCaseManager $fundingCaseManager;

  private ApplicationProcessStatusDeterminerInterface $statusDeterminer;

  private ValidatorInterface $validator;

  public function __construct(
    ApplicationProcessManager $applicationProcessManager,
    ApplicationJsonSchemaFactoryInterface $jsonSchemaFactory,
    FundingCaseManager $fundingCaseManager,
    ApplicationProcessStatusDeterminerInterface $statusDeterminer,
    ValidatorInterface $validator
  ) {
    $this->applicationProcessManager = $applicationProcessManager;
    $this->jsonSchemaFactory = $jsonSchemaFactory;
    $this->fundingCaseManager = $fundingCaseManager;
    $this->statusDeterminer = $statusDeterminer;
    $this->validator = $validator;
  }

  public function handle(ApplicationFormNewSubmitCommand $command): ApplicationFormNewSubmitResult {
    $jsonSchema = $this->jsonSchemaFactory->createJsonSchemaInitial(
      $command->getContactId(),
      $command->getFundingProgram(),
      $command->getFundingCaseType(),
    );
    $validationResult = $this->validator->validate($jsonSchema, $command->getData());

    if ($validationResult->isValid()) {
      return $this->handleValid($command, $validationResult);
    }
    else {
      return ApplicationFormNewSubmitResult::createError($validationResult);
    }
  }

  private function handleValid(
    ApplicationFormNewSubmitCommand $command,
    ValidationResult $validationResult
  ): ApplicationFormNewSubmitResult {
    $validatedData = $this->jsonSchemaFactory->createNewValidatedData(
      $command->getFundingCaseType(),
      $validationResult
    );
    $fundingCase = $this->fundingCaseManager->create($command->getContactId(), [
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

    return ApplicationFormNewSubmitResult::createSuccess(
      $validationResult,
      $validatedData,
      $applicationProcess,
      $fundingCase
    );
  }

}
