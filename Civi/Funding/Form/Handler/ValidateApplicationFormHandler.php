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

use Civi\Funding\Event\Remote\AbstractFundingValidateFormEvent;
use Civi\Funding\Event\Remote\ApplicationProcess\ValidateApplicationFormEvent;
use Civi\Funding\Event\Remote\FundingCase\ValidateNewApplicationFormEvent;
use Civi\Funding\Form\ApplicationFormFactoryInterface;
use Civi\Funding\Form\Validation\FormValidatorInterface;
use Civi\Funding\Form\Validation\ValidationResult;

final class ValidateApplicationFormHandler implements ValidateApplicationFormHandlerInterface {

  private ApplicationFormFactoryInterface $formFactory;

  private FormValidatorInterface $validator;

  public function __construct(
    ApplicationFormFactoryInterface $formFactory,
    FormValidatorInterface $validator
  ) {
    $this->formFactory = $formFactory;
    $this->validator = $validator;
  }

  public function handleValidateForm(ValidateApplicationFormEvent $event): void {
    $form = $this->formFactory->createFormOnValidate($event);
    $validationResult = $this->validator->validate($form);
    $this->mapValidationResultToEvent($validationResult, $event);
  }

  public function handleValidateNewForm(ValidateNewApplicationFormEvent $event): void {
    $form = $this->formFactory->createNewFormOnValidate($event);
    $validationResult = $this->validator->validate($form);
    $this->mapValidationResultToEvent($validationResult, $event);
  }

  public function supportsFundingCaseType(string $fundingCaseType): bool {
    return $this->formFactory->supportsFundingCaseType($fundingCaseType);
  }

  private function mapValidationResultToEvent(
    ValidationResult $validationResult,
    AbstractFundingValidateFormEvent $event
  ): void {
    if ($validationResult->isValid()) {
      $event->setValid(TRUE);
    }
    else {
      foreach ($validationResult->getLeafErrorMessages() as $jsonPointer => $messages) {
        // TODO: Change and translate message
        $event->addErrorsAt($jsonPointer, $messages);
      }
    }
  }

}
