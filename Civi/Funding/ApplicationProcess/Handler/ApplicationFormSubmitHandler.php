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
use Civi\Funding\ApplicationProcess\Command\ApplicationFormSubmitCommand;
use Civi\Funding\ApplicationProcess\Command\ApplicationFormSubmitResult;
use Civi\Funding\ApplicationProcess\StatusDeterminer\ApplicationProcessStatusDeterminerInterface;
use Civi\Funding\Form\ApplicationJsonSchemaFactoryInterface;
use Civi\Funding\Form\Validation\ValidationResult;
use Civi\Funding\Form\Validation\ValidatorInterface;
use Civi\RemoteTools\Form\JsonSchema\JsonSchema;

final class ApplicationFormSubmitHandler implements ApplicationFormSubmitHandlerInterface {

  private ApplicationProcessManager $applicationProcessManager;

  private ApplicationJsonSchemaFactoryInterface $jsonSchemaFactory;

  private ApplicationProcessStatusDeterminerInterface $statusDeterminer;

  private ValidatorInterface $validator;

  public function __construct(
    ApplicationProcessManager $applicationProcessManager,
    ApplicationJsonSchemaFactoryInterface $jsonSchemaFactory,
    ApplicationProcessStatusDeterminerInterface $statusDeterminer,
    ValidatorInterface $validator
  ) {
    $this->applicationProcessManager = $applicationProcessManager;
    $this->jsonSchemaFactory = $jsonSchemaFactory;
    $this->statusDeterminer = $statusDeterminer;
    $this->validator = $validator;
  }

  public function handle(ApplicationFormSubmitCommand $command): ApplicationFormSubmitResult {
    $jsonSchema = $this->jsonSchemaFactory->createJsonSchemaExisting($command->getApplicationProcessBundle());
    $validationResult = $this->validator->validate($jsonSchema, $command->getData());

    if ($validationResult->isValid()) {
      return $this->handleValid($command, $jsonSchema, $validationResult);
    }

    return ApplicationFormSubmitResult::createError($validationResult);
  }

  private function handleValid(
    ApplicationFormSubmitCommand $command,
    JsonSchema $jsonSchema,
    ValidationResult $validationResult
  ): ApplicationFormSubmitResult {
    $applicationProcess = $command->getApplicationProcess();
    $validatedData = $this->jsonSchemaFactory->createValidatedData(
      $applicationProcess,
      $command->getFundingCaseType(),
      $validationResult
    );

    if ('delete' === $validatedData->getAction()) {
      $this->applicationProcessManager->delete($command->getApplicationProcessBundle());

      return ApplicationFormSubmitResult::createSuccess($validationResult, $validatedData);
    }

    $applicationProcess->setFullStatus(
      $this->statusDeterminer->getStatus($applicationProcess->getFullStatus(), $validatedData->getAction())
    );
    if (FALSE === $jsonSchema->getKeywordValueOrDefault('readOnly', FALSE)) {
      $applicationProcess->setTitle($validatedData->getTitle());
      $applicationProcess->setShortDescription($validatedData->getShortDescription());
      $applicationProcess->setStartDate($validatedData->getStartDate());
      $applicationProcess->setEndDate($validatedData->getEndDate());
      $applicationProcess->setAmountRequested($validatedData->getAmountRequested());
      $applicationProcess->setRequestData($validatedData->getApplicationData());
    }

    $this->applicationProcessManager->update(
      $command->getContactId(),
      $command->getApplicationProcessBundle(),
    );

    return ApplicationFormSubmitResult::createSuccess($validationResult, $validatedData);
  }

}
