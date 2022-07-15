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

use Civi\Api4\Generic\AbstractGetAction;
use Civi\Api4\Generic\Result;
use Civi\Core\CiviEventDispatcher;
use Civi\RemoteTools\Api4\Action\Traits\CreateActionEventTrait;
use Civi\RemoteTools\Api4\Action\Traits\EventActionTrait;
use Civi\RemoteTools\Event\GetEvent;

class EventGetAction extends AbstractGetAction implements EventActionInterface {

  use CreateActionEventTrait;

  use EventActionTrait;

  public function __construct(string $initRequestEventName, string $authorizeRequestEventName,
                              string $entityName, string $actionName = 'get',
                              CiviEventDispatcher $eventDispatcher = NULL) {
    parent::__construct($entityName, $actionName);
    $this->_initRequestEventName = $initRequestEventName;
    $this->_authorizeRequestEventName = $authorizeRequestEventName;
    $this->_eventDispatcher = $eventDispatcher ?? \Civi::dispatcher();
  }

  /**
   * @inheritDoc
   */
  public function _run(Result $result): void {
    /** @var \Civi\RemoteTools\Event\GetEvent $event */
    $event = $this->createEvent();
    $this->dispatchEvent($event);

    $result->debug['event'] = $event->getDebugOutput();
    $result->rowCount = $event->getRowCount();
    $result->exchangeArray($event->getRecords());
  }

  /**
   * @inheritDoc
   */
  protected function getEventClass(): string {
    return GetEvent::class;
  }

}
