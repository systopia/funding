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
use Civi\API\Events;
use Civi\Api4\Generic\AbstractAction;
use Civi\Core\CiviEventDispatcherInterface;
use Civi\RemoteTools\Api4\Action\EventActionInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ApiAuthorizeSubscriber implements EventSubscriberInterface {

  /**
   * Note: We cannot use the event dispatcher given as third parameter of the
   * listener method.
   *
   * @see https://lab.civicrm.org/dev/core/-/issues/2316#note_87197
   */
  protected CiviEventDispatcherInterface $eventDispatcher;

  public static function getSubscribedEvents(): array {
    return [
      'civi.api.authorize' => ['onApiAuthorize', Events::W_EARLY],
    ];
  }

  public function __construct(CiviEventDispatcherInterface $eventDispatcher) {
    $this->eventDispatcher = $eventDispatcher;
  }

  public function onApiAuthorize(AuthorizeEvent $event): void {
    $request = $event->getApiRequest();
    if (!$request instanceof EventActionInterface) {
      return;
    }

    $authorized = $this->isApiRequestAuthorized($request);
    if (NULL !== $authorized) {
      $event->setAuthorized($authorized);
      $event->stopPropagation();
    }
  }

  protected function isApiRequestAuthorized(AbstractAction $request): ?bool {
    if (!$request instanceof EventActionInterface) {
      return FALSE;
    }

    /** @var \Civi\RemoteTools\Event\AuthorizeApiRequestEvent $authorizeRequestEvent */
    $authorizeRequestEvent = $request->getAuthorizeRequestEventClass()::fromApiRequest($request);
    $this->eventDispatcher->dispatch($request->getAuthorizeRequestEventName(), $authorizeRequestEvent);

    return $authorizeRequestEvent->isAuthorized();
  }

}
