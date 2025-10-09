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

namespace Civi\RemoteTools\Api4\Action\Traits;

use Civi\Funding\Api4\Action\Traits\EventDispatcherTrait;
use Civi\RemoteTools\Api4\Action\EventActionInterface;
use Civi\RemoteTools\Event\AbstractRequestEvent;
use Civi\RemoteTools\Event\AuthorizeApiRequestEvent;
use Civi\RemoteTools\Event\InitApiRequestEvent;

trait EventActionTrait {

  use EventDispatcherTrait;

  protected string $_authorizeRequestEventName;

  /**
   * @var array<string, mixed>
   */
  protected array $_extraParams = [];

  protected string $_initRequestEventName;

  /**
   * @noinspection PhpParamsInspection
   */
  protected function dispatchEvent(AbstractRequestEvent $event): void {
    /** @var \Civi\Api4\Generic\AbstractAction $this */
    $this->getEventDispatcher()->dispatch($event::getEventName($this->getEntityName(), $this->getActionName()), $event);
    $this->getEventDispatcher()->dispatch($event::getEventName($this->getEntityName()), $event);
    $this->getEventDispatcher()->dispatch($event::getEventName(), $event);
  }

  public function getAuthorizeRequestEventClass(): string {
    return AuthorizeApiRequestEvent::class;
  }

  public function getAuthorizeRequestEventName(): string {
    return $this->_authorizeRequestEventName;
  }

  public function getInitRequestEventClass(): string {
    return InitApiRequestEvent::class;
  }

  public function getInitRequestEventName(): string {
    return $this->_initRequestEventName;
  }

  public function getExtraParam(string $key) {
    return $this->_extraParams[$key] ?? NULL;
  }

  public function getExtraParams(): array {
    return $this->_extraParams;
  }

  public function hasExtraParam(string $key): bool {
    return isset($this->_extraParams[$key]);
  }

  public function setExtraParam(string $key, $value): EventActionInterface {
    $this->_extraParams[$key] = $value;

    return $this;
  }

  public function setExtraParams(array $extraParams): EventActionInterface {
    $this->_extraParams = $extraParams;

    return $this;
  }

  public function getRequiredExtraParams(): array {
    return [];
  }

}
