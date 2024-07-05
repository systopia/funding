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

namespace Civi\Funding\EventSubscriber\Form;

use Civi\Funding\ApplicationProcess\ApplicationProcessBundleLoader;
use Civi\Funding\ApplicationProcess\Command\ApplicationFormNewValidateCommand;
use Civi\Funding\ApplicationProcess\Command\ApplicationFormValidateCommand;
use Civi\Funding\ApplicationProcess\Form\Validation\ApplicationFormValidationResult;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormNewValidateHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormValidateHandlerInterface;
use Civi\Funding\Event\Remote\AbstractFundingValidateFormEvent;
use Civi\Funding\Event\Remote\ApplicationProcess\ValidateApplicationFormEvent;
use Civi\Funding\Event\Remote\FundingCase\ValidateNewApplicationFormEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ValidateApplicationFormSubscriber implements EventSubscriberInterface {

  private ApplicationProcessBundleLoader $applicationProcessBundleLoader;

  private ApplicationFormNewValidateHandlerInterface $newValidateHandler;

  private ApplicationFormValidateHandlerInterface $validateHandler;

  public function __construct(
    ApplicationProcessBundleLoader $applicationProcessBundleLoader,
    ApplicationFormNewValidateHandlerInterface $newValidateHandler,
    ApplicationFormValidateHandlerInterface $validateHandler
  ) {
    $this->applicationProcessBundleLoader = $applicationProcessBundleLoader;
    $this->newValidateHandler = $newValidateHandler;
    $this->validateHandler = $validateHandler;
  }

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    return [
      ValidateApplicationFormEvent::getEventName() => 'onValidateForm',
      ValidateNewApplicationFormEvent::getEventName() => 'onValidateNewForm',
    ];
  }

  public function onValidateForm(ValidateApplicationFormEvent $event): void {
    $statusList = $this->applicationProcessBundleLoader->getStatusList($event->getApplicationProcessBundle());

    $command = new ApplicationFormValidateCommand(
      $event->getApplicationProcessBundle(),
      $statusList,
      $event->getData(),
      20,
    );

    $result = $this->validateHandler->handle($command);
    $this->mapValidationResultToEvent($result, $event);
  }

  public function onValidateNewForm(ValidateNewApplicationFormEvent $event): void {
    $command = new ApplicationFormNewValidateCommand(
      $event->getContactId(),
      $event->getFundingProgram(),
      $event->getFundingCaseType(),
      $event->getData()
    );

    $result = $this->newValidateHandler->handle($command);
    $this->mapValidationResultToEvent($result, $event);
  }

  private function mapValidationResultToEvent(
    ApplicationFormValidationResult $validationResult,
    AbstractFundingValidateFormEvent $event
  ): void {
    if ($validationResult->isValid()) {
      $event->setValid(TRUE);
    }
    else {
      foreach ($validationResult->getErrorMessages() as $jsonPointer => $messages) {
        $event->addErrorsAt($jsonPointer, $messages);
      }
    }
  }

}
