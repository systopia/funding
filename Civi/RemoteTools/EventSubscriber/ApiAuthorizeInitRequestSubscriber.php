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

use Civi\API\Event\AuthorizeEvent;
use Civi\Core\CiviEventDispatcher;
use Civi\RemoteTools\Api4\Action\EventActionInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class ApiAuthorizeInitRequestSubscriber implements EventSubscriberInterface {

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    // highest priority so that the init request event is dispatched before
    // the authorize event is actually handled
    return ['civi.api.authorize' => ['onApiAuthorize', PHP_INT_MAX]];
  }

  /**
   * @param \Civi\API\Event\AuthorizeEvent $event
   * @param string $eventName
   * @param \Civi\Core\CiviEventDispatcher $eventDispatcher
   *
   * @throws \API_Exception
   */
  public function onApiAuthorize(AuthorizeEvent $event, string $eventName, CiviEventDispatcher $eventDispatcher): void {
    $request = $event->getApiRequest();
    if ($request instanceof EventActionInterface) {
      $initRequestEvent = $request->getInitRequestEventClass()::fromApiRequest($request);
      $eventDispatcher->dispatch($request->getInitRequestEventName(), $initRequestEvent);
      $this->assertExtraParams($request);
    }
  }

  /**
   * @param \Civi\RemoteTools\Api4\Action\EventActionInterface $request
   *
   * @throws \API_Exception
   */
  private function assertExtraParams(EventActionInterface $request): void {
    foreach ($request->getRequiredExtraParams() as $key) {
      if (!$request->hasExtraParam($key)) {
        throw new \API_Exception(sprintf('Required extra param "%s" is missing', $key));
      }
    }
  }

}
