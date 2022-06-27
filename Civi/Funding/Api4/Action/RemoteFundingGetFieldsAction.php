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

use Civi\Funding\Api4\Action\Traits\RemoteFundingActionContactIdTrait;
use Civi\Funding\Event\FundingEvents;
use Civi\Funding\Event\RemoteFundingGetFieldsEvent;
use Civi\RemoteTools\Api4\Action\EventGetFieldsAction;
use Civi\Core\CiviEventDispatcher;

class RemoteFundingGetFieldsAction extends EventGetFieldsAction implements RemoteFundingActionInterface {

  use RemoteFundingActionContactIdTrait;

  public function __construct(string $entityName, string $actionName = 'getFields',
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
    return RemoteFundingGetFieldsEvent::class;
  }

}
