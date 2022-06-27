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

namespace Civi\Funding\Api4\Action;

use Civi\Core\CiviEventDispatcher;
use Civi\Funding\Api4\Action\Traits\RemoteFundingActionContactIdRequiredTrait;
use Civi\Funding\Event\FundingEvents;
use Civi\Funding\Event\RemoteFundingDAOGetEvent;
use Civi\RemoteTools\Api4\Action\EventDAOGetAction;

class RemoteFundingDAOGetAction extends EventDAOGetAction implements RemoteFundingActionInterface {

  use RemoteFundingActionContactIdRequiredTrait;

  public function __construct(string $entityName, string $actionName = 'get',
    CiviEventDispatcher $eventDispatcher = NULL
  ) {
    parent::__construct(FundingEvents::REMOTE_REQUEST_INIT_EVENT_NAME,
      FundingEvents::REMOTE_REQUEST_AUTHORIZE_EVENT_NAME,
      $entityName, $actionName, $eventDispatcher);
  }

  /**
   * @noinspection PhpMissingParentCallCommonInspection
   */
  protected function getEventClass(): string {
    return RemoteFundingDAOGetEvent::class;
  }

}
