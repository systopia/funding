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
use Civi\Funding\ApplicationProcess\Command\ApplicationFormCreateCommand;
use Civi\Funding\ApplicationProcess\Command\ApplicationFormNewCreateCommand;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormCreateHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormNewCreateHandlerInterface;
use Civi\Funding\Event\Remote\AbstractFundingGetFormEvent;
use Civi\Funding\Event\Remote\ApplicationProcess\GetApplicationFormEvent;
use Civi\Funding\Event\Remote\FundingCase\GetNewApplicationFormEvent;
use Civi\RemoteTools\Form\RemoteFormInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class GetApplicationFormSubscriber implements EventSubscriberInterface {

  private ApplicationProcessBundleLoader $applicationProcessBundleLoader;

  private ApplicationFormCreateHandlerInterface $createHandler;

  private ApplicationFormNewCreateHandlerInterface $newCreateHandler;

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    return [
      GetApplicationFormEvent::getEventName() => 'onGetForm',
      GetNewApplicationFormEvent::getEventName() => 'onGetNewForm',
    ];
  }

  public function __construct(
    ApplicationProcessBundleLoader $applicationProcessBundleLoader,
    ApplicationFormCreateHandlerInterface $createHandler,
    ApplicationFormNewCreateHandlerInterface $newCreateHandler
  ) {
    $this->applicationProcessBundleLoader = $applicationProcessBundleLoader;
    $this->createHandler = $createHandler;
    $this->newCreateHandler = $newCreateHandler;
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function onGetForm(GetApplicationFormEvent $event): void {
    $statusList = $this->applicationProcessBundleLoader->getStatusList($event->getApplicationProcessBundle());

    $form = $this->createHandler->handle(
      new ApplicationFormCreateCommand($event->getApplicationProcessBundle(), $statusList)
    );
    $this->mapFormToEvent($form, $event);
  }

  public function onGetNewForm(GetNewApplicationFormEvent $event): void {
    $form = $this->newCreateHandler->handle(
      new ApplicationFormNewCreateCommand(
        $event->getContactId(),
        $event->getFundingCaseType(),
        $event->getFundingProgram(),
      )
    );
    $this->mapFormToEvent($form, $event);
  }

  private function mapFormToEvent(RemoteFormInterface $form, AbstractFundingGetFormEvent $event): void {
    $event->setData($form->getData());
    $event->setJsonSchema($form->getJsonSchema());
    $event->setUiSchema($form->getUiSchema());
  }

}
