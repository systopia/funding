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

use Civi\Funding\ApplicationProcess\Command\ApplicationFormCreateCommand;
use Civi\Funding\ApplicationProcess\Command\ApplicationFormNewSubmitCommand;
use Civi\Funding\ApplicationProcess\Command\ApplicationFormSubmitCommand;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormCreateHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormNewSubmitHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormSubmitHandlerInterface;
use Civi\Funding\Event\Remote\AbstractFundingSubmitFormEvent;
use Civi\Funding\Event\Remote\ApplicationProcess\SubmitApplicationFormEvent;
use Civi\Funding\Event\Remote\FundingCase\SubmitNewApplicationFormEvent;
use Civi\Funding\Form\Validation\ValidationResult;
use CRM_Funding_ExtensionUtil as E;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Webmozart\Assert\Assert;

class SubmitApplicationFormSubscriber implements EventSubscriberInterface {

  private ApplicationFormCreateHandlerInterface $createHandler;

  private ApplicationFormNewSubmitHandlerInterface $newSubmitHandler;

  private ApplicationFormSubmitHandlerInterface $submitHandler;

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    return [
      SubmitApplicationFormEvent::getEventName() => 'onSubmitForm',
      SubmitNewApplicationFormEvent::getEventName() => 'onSubmitNewForm',
    ];
  }

  public function __construct(
    ApplicationFormCreateHandlerInterface $createHandler,
    ApplicationFormNewSubmitHandlerInterface $newSubmitHandler,
    ApplicationFormSubmitHandlerInterface $submitHandler
  ) {
    $this->createHandler = $createHandler;
    $this->newSubmitHandler = $newSubmitHandler;
    $this->submitHandler = $submitHandler;
  }

  public function onSubmitForm(SubmitApplicationFormEvent $event): void {
    $command = new ApplicationFormSubmitCommand(
      $event->getContactId(),
      $event->getApplicationProcess(),
      $event->getFundingCase(),
      $event->getFundingCaseType(),
      $event->getFundingProgram(),
      $event->getData()
    );

    $result = $this->submitHandler->handle($command);
    if ($result->isSuccess()) {
      // TODO: Change message
      $event->setMessage(E::ts('Success!'));
      Assert::notNull($result->getValidatedData());
      if ($this->isShouldShowForm($result->getValidatedData()->getAction())) {
        $event->setForm(
          $this->createHandler->handle(new ApplicationFormCreateCommand(
            $event->getApplicationProcess(),
            $event->getFundingCase(),
            $event->getFundingCaseType(),
            $event->getFundingProgram(),
            $result->getValidationResult()->getData(),
          ))
        );
      }
      else {
        $event->setAction($event::ACTION_CLOSE_FORM);
      }
    }
    else {
      $this->mapValidationErrorsToEvent($result->getValidationResult(), $event);
    }
  }

  public function onSubmitNewForm(SubmitNewApplicationFormEvent $event): void {
    $command = new ApplicationFormNewSubmitCommand(
      $event->getContactId(),
      $event->getFundingCaseType(),
      $event->getFundingProgram(),
      $event->getData()
    );

    $result = $this->newSubmitHandler->handle($command);
    if ($result->isSuccess()) {
      // TODO: Change message
      $event->setMessage(E::ts('Success!'));
      Assert::notNull($result->getValidatedData());
      if ($this->isShouldShowForm($result->getValidatedData()->getAction())) {
        Assert::notNull($result->getApplicationProcess());
        Assert::notNull($result->getFundingCase());
        $event->setForm(
          $this->createHandler->handle(new ApplicationFormCreateCommand(
            $result->getApplicationProcess(),
            $result->getFundingCase(),
            $event->getFundingCaseType(),
            $event->getFundingProgram(),
            $result->getValidationResult()->getData(),
          ))
        );
      }
      else {
        $event->setAction($event::ACTION_CLOSE_FORM);
      }
    }
    else {
      $this->mapValidationErrorsToEvent($result->getValidationResult(), $event);
    }
  }

  private function isShouldShowForm(string $action): bool {
    return in_array($action, ['save', 'modify', 'update'], TRUE);
  }

  private function mapValidationErrorsToEvent(
    ValidationResult $validationResult,
    AbstractFundingSubmitFormEvent $event
  ): void {
    // TODO: Change message
    $event->setMessage(E::ts('Validation failed'));
    foreach ($validationResult->getLeafErrorMessages() as $jsonPointer => $messages) {
      $event->addErrorsAt($jsonPointer, $messages);
    }
  }

}
