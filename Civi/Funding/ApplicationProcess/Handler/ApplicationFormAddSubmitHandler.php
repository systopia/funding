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
use Civi\Funding\ApplicationProcess\Command\ApplicationFormAddSubmitCommand;
use Civi\Funding\ApplicationProcess\Command\ApplicationFormAddSubmitResult;
use Civi\Funding\ApplicationProcess\Command\ApplicationFormAddValidateCommand;
use Civi\Funding\ApplicationProcess\StatusDeterminer\ApplicationProcessStatusDeterminerInterface;
use Civi\Funding\Entity\ApplicationProcessEntityBundle;
use Civi\Funding\Form\Application\ApplicationValidationResult;

final class ApplicationFormAddSubmitHandler implements ApplicationFormAddSubmitHandlerInterface {

  private ApplicationProcessManager $applicationProcessManager;

  private ApplicationProcessStatusDeterminerInterface $statusDeterminer;

  private ApplicationFormAddValidateHandlerInterface $validateHandler;

  public function __construct(
    ApplicationProcessManager $applicationProcessManager,
    ApplicationProcessStatusDeterminerInterface $statusDeterminer,
    ApplicationFormAddValidateHandlerInterface $validateHandler
  ) {
    $this->applicationProcessManager = $applicationProcessManager;
    $this->statusDeterminer = $statusDeterminer;
    $this->validateHandler = $validateHandler;
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function handle(ApplicationFormAddSubmitCommand $command): ApplicationFormAddSubmitResult {
    $validationResult = $this->validateHandler->handle(new ApplicationFormAddValidateCommand(
      $command->getContactId(),
      $command->getFundingProgram(),
      $command->getFundingCaseType(),
      $command->getFundingCase(),
      $command->getData(),
    ));

    if ($validationResult->isValid()) {
      return $this->handleValid($command, $validationResult);
    }
    else {
      return ApplicationFormAddSubmitResult::createError($validationResult);
    }
  }

  /**
   * @throws \CRM_Core_Exception
   */
  private function handleValid(
    ApplicationFormAddSubmitCommand $command,
    ApplicationValidationResult $validationResult
  ): ApplicationFormAddSubmitResult {
    $validatedData = $validationResult->getValidatedData();

    $applicationProcess = $this->applicationProcessManager->create(
      $command->getContactId(),
      $command->getFundingCase(),
      $command->getFundingCaseType(),
      $command->getFundingProgram(),
      $this->statusDeterminer->getInitialStatus($validatedData->getAction()),
      $validatedData,
    );
    $applicationProcessBundle = new ApplicationProcessEntityBundle(
      $applicationProcess,
      $command->getFundingCase(),
      $command->getFundingCaseType(),
      $command->getFundingProgram(),
    );

    return ApplicationFormAddSubmitResult::createSuccess(
      $validationResult,
      $applicationProcessBundle,
    );
  }

}
