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

namespace Civi\Funding\EventSubscriber\ApplicationProcess;

use Civi\Funding\ApplicationProcess\Command\ApplicationCostItemsAddIdentifiersCommand;
use Civi\Funding\ApplicationProcess\Command\ApplicationCostItemsPersistCommand;
use Civi\Funding\ApplicationProcess\Handler\ApplicationCostItemsAddIdentifiersHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationCostItemsPersistHandlerInterface;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessCreatedEvent;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessPreCreateEvent;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessPreUpdateEvent;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessUpdatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ApplicationCostItemsSubscriber implements EventSubscriberInterface {

  private ApplicationCostItemsAddIdentifiersHandlerInterface $costItemsAddIdentifiersHandler;

  private ApplicationCostItemsPersistHandlerInterface $costItemsPersistHandler;

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    return [
      ApplicationProcessPreCreateEvent::class => 'onPreCreate',
      ApplicationProcessCreatedEvent::class => 'onCreated',
      ApplicationProcessPreUpdateEvent::class => 'onPreUpdate',
      ApplicationProcessUpdatedEvent::class => 'onUpdated',
    ];
  }

  public function __construct(
    ApplicationCostItemsAddIdentifiersHandlerInterface $costItemsAddIdentifiersHandler,
    ApplicationCostItemsPersistHandlerInterface $costItemsPersistHandler
  ) {
    $this->costItemsAddIdentifiersHandler = $costItemsAddIdentifiersHandler;
    $this->costItemsPersistHandler = $costItemsPersistHandler;
  }

  public function onPreCreate(ApplicationProcessPreCreateEvent $event): void {
    $requestData = $this->costItemsAddIdentifiersHandler->handle(new ApplicationCostItemsAddIdentifiersCommand(
      $event->getApplicationProcess(),
      $event->getFundingCase(),
      $event->getFundingCaseType()
    ));

    $event->getApplicationProcess()->setRequestData($requestData);
  }

  public function onCreated(ApplicationProcessCreatedEvent $event): void {
    $this->costItemsPersistHandler->handle(new ApplicationCostItemsPersistCommand(
      $event->getApplicationProcess(),
      $event->getFundingCase(),
      $event->getFundingCaseType(),
      NULL
    ));
  }

  public function onPreUpdate(ApplicationProcessPreUpdateEvent $event): void {
    $requestData = $this->costItemsAddIdentifiersHandler->handle(new ApplicationCostItemsAddIdentifiersCommand(
      $event->getApplicationProcess(),
      $event->getFundingCase(),
      $event->getFundingCaseType()
    ));

    $event->getApplicationProcess()->setRequestData($requestData);
  }

  public function onUpdated(ApplicationProcessUpdatedEvent $event): void {
    $this->costItemsPersistHandler->handle(new ApplicationCostItemsPersistCommand(
      $event->getApplicationProcess(),
      $event->getFundingCase(),
      $event->getFundingCaseType(),
      $event->getPreviousApplicationProcess()
    ));
  }

}
