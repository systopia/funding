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

namespace Civi\RemoteTools\EventSubscriber;

use Civi\API\Events;
use Civi\Core\CiviEventDispatcher;
use Civi\RemoteTools\Api4\Action\EventActionInterface;
use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\Event\CheckAccessEvent;

class CheckAccessSubscriber extends ApiAuthorizeSubscriber {

  protected Api4Interface $api4;

  /**
   * @noinspection PhpMissingParentCallCommonInspection
   */
  public static function getSubscribedEvents(): array {
    return [
      CheckAccessEvent::getEventName() => ['onCheckAccess', Events::W_EARLY],
    ];
  }

  public function __construct(Api4Interface $api4, CiviEventDispatcher $eventDispatcher) {
    parent::__construct($eventDispatcher);
    $this->api4 = $api4;
  }

  /**
   * @throws \Civi\API\Exception\NotImplementedException
   */
  public function onCheckAccess(CheckAccessEvent $event): void {
    $event->addDebugOutput(static::class, []);
    $apiRequest = $this->api4->createAction($event->getEntityName(), $event->getAction(), $event->getRequestParams());

    if ($apiRequest instanceof EventActionInterface) {
      $initRequestEvent = $apiRequest->getInitRequestEventClass()::fromApiRequest($apiRequest);
      $this->eventDispatcher->dispatch($apiRequest->getInitRequestEventName(), $initRequestEvent);
    }

    if (FALSE === $this->isApiRequestAuthorized($apiRequest)) {
      $event->setAccessGranted(FALSE);
      $event->stopPropagation();
    }
  }

}
