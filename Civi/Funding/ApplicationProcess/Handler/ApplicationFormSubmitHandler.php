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

use Civi\Funding\ApplicationProcess\ActionStatusInfo\ApplicationProcessActionStatusInfoInterface;
use Civi\Funding\ApplicationProcess\ApplicationProcessManager;
use Civi\Funding\ApplicationProcess\Command\ApplicationFormCommentPersistCommand;
use Civi\Funding\ApplicationProcess\Command\ApplicationFormSubmitCommand;
use Civi\Funding\ApplicationProcess\Command\ApplicationFormSubmitResult;
use Civi\Funding\ApplicationProcess\Snapshot\ApplicationSnapshotRestorerInterface;
use Civi\Funding\ApplicationProcess\StatusDeterminer\ApplicationProcessStatusDeterminerInterface;
use Civi\Funding\Entity\ApplicationProcessEntity;
use Civi\Funding\Form\ApplicationValidationResult;
use Civi\Funding\Form\ApplicationValidatorInterface;

final class ApplicationFormSubmitHandler implements ApplicationFormSubmitHandlerInterface {

  private ApplicationProcessManager $applicationProcessManager;

  private ApplicationSnapshotRestorerInterface $applicationSnapshotRestorer;

  private ApplicationFormCommentPersistHandlerInterface $commentPersistHandler;

  private ApplicationProcessActionStatusInfoInterface $info;

  private ApplicationProcessStatusDeterminerInterface $statusDeterminer;

  private ApplicationValidatorInterface $validator;

  public function __construct(
    ApplicationProcessManager $applicationProcessManager,
    ApplicationSnapshotRestorerInterface $applicationSnapshotRestorer,
    ApplicationFormCommentPersistHandlerInterface $commentPersistHandler,
    ApplicationProcessActionStatusInfoInterface $info,
    ApplicationProcessStatusDeterminerInterface $statusDeterminer,
    ApplicationValidatorInterface $validator
  ) {
    $this->applicationProcessManager = $applicationProcessManager;
    $this->applicationSnapshotRestorer = $applicationSnapshotRestorer;
    $this->commentPersistHandler = $commentPersistHandler;
    $this->info = $info;
    $this->statusDeterminer = $statusDeterminer;
    $this->validator = $validator;
  }

  public function handle(ApplicationFormSubmitCommand $command): ApplicationFormSubmitResult {
    $validationResult = $this->validator->validateExisting(
      $command->getApplicationProcessBundle(),
      $command->getApplicationProcessStatusList(),
      $command->getData()
    );
    if ($validationResult->isValid()) {
      return $this->handleValid($command, $validationResult);
    }

    return ApplicationFormSubmitResult::createError($validationResult);
  }

  private function handleValid(
    ApplicationFormSubmitCommand $command,
    ApplicationValidationResult $validationResult
  ): ApplicationFormSubmitResult {
    $validatedData = $validationResult->getValidatedData();

    if ($this->info->isDeleteAction($validatedData->getAction())) {
      $this->applicationProcessManager->delete($command->getApplicationProcessBundle());

      return ApplicationFormSubmitResult::createSuccess($validationResult);
    }

    if ($this->info->isRestoreAction($validatedData->getAction())) {
      $this->applicationSnapshotRestorer->restoreLastSnapshot(
        $command->getContactId(),
        $command->getApplicationProcessBundle()
      );
    }
    else {
      $this->mapValidatedDataIntoApplicationProcess($command->getApplicationProcess(), $validationResult);
      $this->applicationProcessManager->update(
        $command->getContactId(),
        $command->getApplicationProcessBundle(),
      );
    }

    if (NULL !== $validatedData->getComment() && '' !== $validatedData->getComment()['text']) {
      $this->commentPersistHandler->handle(new ApplicationFormCommentPersistCommand(
        $command->getContactId(),
        $command->getApplicationProcess(),
        $command->getFundingCase(),
        $command->getFundingCaseType(),
        $command->getFundingProgram(),
        $validatedData,
      ));
    }

    return ApplicationFormSubmitResult::createSuccess($validationResult);
  }

  private function mapValidatedDataIntoApplicationProcess(
    ApplicationProcessEntity $applicationProcess,
    ApplicationValidationResult $validationResult
  ): void {
    $validatedData = $validationResult->getValidatedData();
    $applicationProcess->setFullStatus(
      $this->statusDeterminer->getStatus($applicationProcess->getFullStatus(), $validatedData->getAction())
    );

    if (!$validationResult->isReadOnly()) {
      $applicationProcess->setTitle($validatedData->getTitle());
      $applicationProcess->setShortDescription($validatedData->getShortDescription());
      $applicationProcess->setStartDate($validatedData->getStartDate());
      $applicationProcess->setEndDate($validatedData->getEndDate());
      $applicationProcess->setAmountRequested($validatedData->getAmountRequested());
      $applicationProcess->setRequestData($validatedData->getApplicationData());
    }
  }

}
