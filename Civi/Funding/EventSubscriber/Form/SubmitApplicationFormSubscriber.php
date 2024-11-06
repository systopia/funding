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

use Civi\Api4\FundingApplicationProcess;
use Civi\Funding\ApplicationProcess\ApplicationProcessBundleLoader;
use Civi\Funding\ApplicationProcess\Command\ApplicationFormNewSubmitCommand;
use Civi\Funding\ApplicationProcess\Command\ApplicationFormSubmitCommand;
use Civi\Funding\ApplicationProcess\Form\Validation\ApplicationFormValidationResult;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormNewSubmitHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormSubmitHandlerInterface;
use Civi\Funding\Entity\ApplicationProcessEntity;
use Civi\Funding\Entity\ExternalFileEntity;
use Civi\Funding\Event\Remote\AbstractFundingSubmitFormEvent;
use Civi\Funding\Event\Remote\ApplicationProcess\SubmitApplicationFormEvent;
use Civi\Funding\Event\Remote\FundingCase\SubmitNewApplicationFormEvent;
use Civi\Funding\Form\RemoteSubmitResponseActions;
use Civi\RemoteTools\Api4\OptionsLoaderInterface;
use CRM_Funding_ExtensionUtil as E;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Webmozart\Assert\Assert;

class SubmitApplicationFormSubscriber implements EventSubscriberInterface {

  private ApplicationProcessBundleLoader $applicationProcessBundleLoader;

  private ApplicationFormNewSubmitHandlerInterface $newSubmitHandler;

  private OptionsLoaderInterface $optionsLoader;

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
    OptionsLoaderInterface $optionsLoader,
    ApplicationFormSubmitHandlerInterface $submitHandler
  ) {
    $this->applicationProcessBundleLoader = $applicationProcessBundleLoader;
    $this->newSubmitHandler = $newSubmitHandler;
    $this->optionsLoader = $optionsLoader;
    $this->submitHandler = $submitHandler;
  }

  /**
   * @throws \CRM_Core_Exception
   */
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
      $event->setMessage($this->createSuccessMessage($event->getApplicationProcessBundle()->getApplicationProcess()));
      $this->addFilesToEvent($result->getFiles(), $event);
      if ($result->getValidationResult()->isReadOnly() && 'delete' !== $result->getValidatedData()->getAction()) {
        $event->setAction(RemoteSubmitResponseActions::RELOAD_FORM);
      }
      else {
        $event->setAction(RemoteSubmitResponseActions::CLOSE_FORM);
      }
    }
    else {
      $this->mapValidationErrorsToEvent($result->getValidationResult(), $event);
    }
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function onSubmitNewForm(SubmitNewApplicationFormEvent $event): void {
    $command = new ApplicationFormNewSubmitCommand(
      $event->getContactId(),
      $event->getFundingCaseType(),
      $event->getFundingProgram(),
      $event->getData()
    );

    $result = $this->newSubmitHandler->handle($command);
    if ($result->isSuccess()) {
      Assert::notNull($result->getApplicationProcessBundle());
      $event->setMessage($this->createSuccessMessage($result->getApplicationProcessBundle()->getApplicationProcess()));
      $this->addFilesToEvent($result->getFiles(), $event);
      $event->setAction(RemoteSubmitResponseActions::CLOSE_FORM);
    }
    else {
      $this->mapValidationErrorsToEvent($result->getValidationResult(), $event);
    }
  }

  private function mapValidationErrorsToEvent(
    ApplicationFormValidationResult $validationResult,
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

  /**
   * @throws \CRM_Core_Exception
   */
  private function createSuccessMessage(ApplicationProcessEntity $applicationProcess): string {
    return E::ts('Saved (Status: %1)', [
      1 => $this->optionsLoader->getOptionLabel(
        FundingApplicationProcess::getEntityName(),
        'status',
        $applicationProcess->getStatus()
      ),
    ]);
  }

}
