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

namespace Civi\Funding\Form\Handler;

use Civi\Funding\ApplicationProcess\ApplicationProcessManager;
use Civi\Funding\ApplicationProcess\ApplicationProcessStatusDeterminer;
use Civi\Funding\Event\Remote\AbstractFundingSubmitFormEvent;
use Civi\Funding\Event\Remote\ApplicationProcess\SubmitApplicationFormEvent;
use Civi\Funding\Event\Remote\FundingCase\SubmitNewApplicationFormEvent;
use Civi\Funding\Form\ApplicationFormInterface;
use Civi\Funding\Form\ApplicationFormFactoryInterface;
use Civi\Funding\Form\Validation\FormValidatorInterface;
use Civi\Funding\Form\Validation\ValidationResult;
use Civi\Funding\FundingCase\FundingCaseManager;
use CRM_Funding_ExtensionUtil as E;

final class SubmitApplicationFormHandler implements SubmitApplicationFormHandlerInterface {

  private ApplicationProcessManager $applicationProcessManager;

  private ApplicationFormFactoryInterface $formFactory;

  private FundingCaseManager $fundingCaseManager;

  private ApplicationProcessStatusDeterminer $statusDeterminer;

  private FormValidatorInterface $validator;

  public function __construct(
    ApplicationProcessManager $applicationProcessManager,
    ApplicationFormFactoryInterface $formFactory,
    FundingCaseManager $fundingCaseManager,
    ApplicationProcessStatusDeterminer $statusDeterminer,
    FormValidatorInterface $validator
  ) {
    $this->applicationProcessManager = $applicationProcessManager;
    $this->formFactory = $formFactory;
    $this->fundingCaseManager = $fundingCaseManager;
    $this->statusDeterminer = $statusDeterminer;
    $this->validator = $validator;
  }

  public function handleSubmitForm(SubmitApplicationFormEvent $event): void {
    $form = $this->formFactory->createFormOnSubmit($event);
    $validationResult = $this->validator->validate($form);

    if ($validationResult->isValid()) {
      $this->handleSubmitValid($event, $form, $validationResult);
    }
    else {
      $this->mapValidationErrorsToEvent($validationResult, $event);
    }
  }

  public function handleSubmitNewForm(SubmitNewApplicationFormEvent $event): void {
    $form = $this->formFactory->createNewFormOnSubmit($event);
    $validationResult = $this->validator->validate($form);

    if ($validationResult->isValid()) {
      $this->handleSubmitNewValid($event, $form, $validationResult);
    }
    else {
      $this->mapValidationErrorsToEvent($validationResult, $event);
    }
  }

  public function supportsFundingCaseType(string $fundingCaseType): bool {
    return $this->formFactory->supportsFundingCaseType($fundingCaseType);
  }

  private function handleSubmitNewValid(
    SubmitNewApplicationFormEvent $event,
    ApplicationFormInterface $form,
    ValidationResult $validationResult
  ): void {
    $validatedData = $this->formFactory->createNewValidatedData($event->getFundingCaseType(), $validationResult);
    $fundingCase = $this->fundingCaseManager->create($event->getContactId(), [
      'funding_program' => $event->getFundingProgram(),
      'funding_case_type' => $event->getFundingCaseType(),
      'recipient_contact_id' => $validatedData->getRecipientContactId(),
    ]);

    $applicationProcess = $this->applicationProcessManager->create(
      $event->getContactId(),
      $fundingCase,
      $this->statusDeterminer->getStatusForNew($validatedData->getAction()),
      $validatedData,
    );

    // TODO: Change message
    $event->setMessage(E::ts('Success!'));
    $event->setForm(
      $this->formFactory->createForm(
        $applicationProcess,
        $event->getFundingProgram(),
        $fundingCase,
        $event->getFundingCaseType(),
      )
    );
  }

  private function handleSubmitValid(
    SubmitApplicationFormEvent $event,
    ApplicationFormInterface $form,
    ValidationResult $validationResult
  ): void {
    $applicationProcess = $event->getApplicationProcess();
    $validatedData = $this->formFactory->createValidatedData(
      $applicationProcess,
      $event->getFundingCaseType(),
      $validationResult
    );
    $applicationProcess->setStatus(
      $this->statusDeterminer->getStatus($applicationProcess->getStatus(), $validatedData->getAction())
    );
    if (!$form->isReadOnly()) {
      $applicationProcess->setTitle($validatedData->getTitle());
      $applicationProcess->setShortDescription($validatedData->getShortDescription());
      $applicationProcess->setStartDate($validatedData->getStartDate());
      $applicationProcess->setEndDate($validatedData->getEndDate());
      $applicationProcess->setAmountRequested($validatedData->getAmountRequested());
      $applicationProcess->setRequestData($validatedData->getApplicationData());
    }

    $this->applicationProcessManager->update($event->getContactId(), $applicationProcess, $event->getFundingCase());

    // TODO: Change message
    $event->setMessage(E::ts('Success!'));
    $event->setForm(
      $this->formFactory->createForm(
        $applicationProcess,
        $event->getFundingProgram(),
        $event->getFundingCase(),
        $event->getFundingCaseType(),
      )
    );
  }

  private function mapValidationErrorsToEvent(
    ValidationResult $validationResult,
    AbstractFundingSubmitFormEvent $event
  ): void {
    // TODO: Change message
    $event->setMessage(E::ts('Validation failed'));
    foreach ($validationResult->getLeafErrorMessages() as $jsonPointer => $messages) {
      // TODO: Change and translate message
      $event->addErrorsAt($jsonPointer, $messages);
    }
  }

}
