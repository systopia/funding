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

use Civi\Funding\ApplicationProcess\Command\ApplicationResourcesItemsAddIdentifiersCommand;
use Civi\Funding\ApplicationProcess\Command\ApplicationResourcesItemsPersistCommand;
use Civi\Funding\ApplicationProcess\Handler\ApplicationResourcesItemsAddIdentifiersHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationResourcesItemsPersistHandlerInterface;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessCreatedEvent;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessPreCreateEvent;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessPreUpdateEvent;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessUpdatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ApplicationResourcesItemsSubscriber implements EventSubscriberInterface {

  private ApplicationResourcesItemsAddIdentifiersHandlerInterface $resourcesItemsAddIdentifiersHandler;

  private ApplicationResourcesItemsPersistHandlerInterface $resourcesItemsPersistHandler;

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
    ApplicationResourcesItemsAddIdentifiersHandlerInterface $resourcesItemsAddIdentifiersHandler,
    ApplicationResourcesItemsPersistHandlerInterface $resourcesItemsPersistHandler
  ) {
    $this->resourcesItemsAddIdentifiersHandler = $resourcesItemsAddIdentifiersHandler;
    $this->resourcesItemsPersistHandler = $resourcesItemsPersistHandler;
  }

  public function onPreCreate(ApplicationProcessPreCreateEvent $event): void {
    $requestData = $this->resourcesItemsAddIdentifiersHandler->handle(
      new ApplicationResourcesItemsAddIdentifiersCommand(
        $event->getApplicationProcessBundle(),
      )
    );

    $event->getApplicationProcess()->setRequestData($requestData);
  }

  public function onCreated(ApplicationProcessCreatedEvent $event): void {
    $this->resourcesItemsPersistHandler->handle(new ApplicationResourcesItemsPersistCommand(
      $event->getApplicationProcessBundle(),
      NULL,
    ));
  }

  public function onPreUpdate(ApplicationProcessPreUpdateEvent $event): void {
    $requestData = $this->resourcesItemsAddIdentifiersHandler->handle(
      new ApplicationResourcesItemsAddIdentifiersCommand(
        $event->getApplicationProcessBundle(),
      )
    );

    $event->getApplicationProcess()->setRequestData($requestData);
  }

  public function onUpdated(ApplicationProcessUpdatedEvent $event): void {
    $this->resourcesItemsPersistHandler->handle(new ApplicationResourcesItemsPersistCommand(
      $event->getApplicationProcessBundle(),
      $event->getPreviousApplicationProcess(),
    ));
  }

}
