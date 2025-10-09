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

namespace Civi\RemoteTools\Api4\Action;

use Civi\Api4\Generic\CheckAccessAction;
use Civi\Api4\Generic\Result;
use Civi\Core\CiviEventDispatcherInterface;
use Civi\RemoteTools\Api4\Action\Traits\CreateActionEventTrait;
use Civi\RemoteTools\Api4\Action\Traits\EventActionTrait;
use Civi\RemoteTools\Event\CheckAccessEvent;
use Webmozart\Assert\Assert;

class EventCheckAccessAction extends CheckAccessAction implements EventActionInterface {

  use CreateActionEventTrait;

  use EventActionTrait;

  public function __construct(string $initRequestEventName, string $authorizeRequestEventName,
                              string $entityName, string $actionName = 'checkAccess',
                              ?CiviEventDispatcherInterface $eventDispatcher = NULL) {
    parent::__construct($entityName, $actionName);
    $this->_initRequestEventName = $initRequestEventName;
    $this->_authorizeRequestEventName = $authorizeRequestEventName;
    $this->_eventDispatcher = $eventDispatcher;
  }

  /**
   * @inheritDoc
   */
  public function _run(Result $result): void {
    parent::_run($result);

    Assert::isArray($result[0]);
    if (!in_array($this->action, ['checkAccess', 'getActions'], TRUE) && $result[0]['access']) {
      $this->checkRemoteAccessGranted($result);
    }
  }

  /**
   * @inheritDoc
   */
  protected function getEventClass(): string {
    return CheckAccessEvent::class;
  }

  private function checkRemoteAccessGranted(Result $result): void {
    /** @var \Civi\RemoteTools\Event\CheckAccessEvent $event */
    $event = $this->createEvent();

    if (CheckAccessEvent::class !== get_class($event)) {
      $this->getEventDispatcher()->dispatch(CheckAccessEvent::getEventName(), $event);
    }
    $this->dispatchEvent($event);

    $result->debug['event'] = $event->getDebugOutput();
    $result->exchangeArray([['access' => FALSE !== $event->isAccessGranted()]]);
  }

}
