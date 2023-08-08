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
use Civi\Funding\ApplicationProcess\Command\ApplicationFormNewSubmitCommand;
use Civi\Funding\ApplicationProcess\Command\ApplicationFormSubmitCommand;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormNewSubmitHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormSubmitHandlerInterface;
use Civi\Funding\Entity\ExternalFileEntity;
use Civi\Funding\Event\Remote\AbstractFundingSubmitFormEvent;
use Civi\Funding\Event\Remote\ApplicationProcess\SubmitApplicationFormEvent;
use Civi\Funding\Event\Remote\FundingCase\SubmitNewApplicationFormEvent;
use Civi\Funding\Form\ApplicationValidationResult;
use CRM_Funding_ExtensionUtil as E;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SubmitApplicationFormSubscriber implements EventSubscriberInterface {

  private ApplicationProcessBundleLoader $applicationProcessBundleLoader;

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
    ApplicationProcessBundleLoader $applicationProcessBundleLoader,
    ApplicationFormNewSubmitHandlerInterface $newSubmitHandler,
    ApplicationFormSubmitHandlerInterface $submitHandler
  ) {
    $this->applicationProcessBundleLoader = $applicationProcessBundleLoader;
    $this->newSubmitHandler = $newSubmitHandler;
    $this->submitHandler = $submitHandler;
  }

  public function onSubmitForm(SubmitApplicationFormEvent $event): void {
    $statusList = $this->applicationProcessBundleLoader->getStatusList($event->getApplicationProcessBundle());

    $command = new ApplicationFormSubmitCommand(
      $event->getContactId(),
      $event->getApplicationProcessBundle(),
      $statusList,
      $event->getData(),
    );

    $result = $this->submitHandler->handle($command);
    if ($result->isSuccess()) {
      $event->setMessage(E::ts('Saved'));
      $this->addFilesToEvent($result->getFiles(), $event);
      $event->setAction($event::ACTION_CLOSE_FORM);
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
      $event->setMessage(E::ts('Saved'));
      $this->addFilesToEvent($result->getFiles(), $event);
      $event->setAction($event::ACTION_CLOSE_FORM);
    }
    else {
      $this->mapValidationErrorsToEvent($result->getValidationResult(), $event);
    }
  }

  private function mapValidationErrorsToEvent(
    ApplicationValidationResult $validationResult,
    AbstractFundingSubmitFormEvent $event
  ): void {
    $event->setMessage(E::ts('Validation failed'));
    foreach ($validationResult->getErrorMessages() as $jsonPointer => $messages) {
      $event->addErrorsAt($jsonPointer, $messages);
    }
  }

  /**
   * @phpstan-param array<string, \Civi\Funding\Entity\ExternalFileEntity> $files
   */
  private function addFilesToEvent(array $files, AbstractFundingSubmitFormEvent $event): void {
    $event->setFiles(array_map(
      fn (ExternalFileEntity $file) => $file->getUri(),
      $files,
    ));
  }

}
