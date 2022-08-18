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

namespace Civi\Funding\Api4\Action\FundingProgram;

use Civi\Api4\FundingProgram;
use Civi\Api4\Generic\DAOGetAction;
use Civi\Api4\Generic\Result;
use Civi\Core\CiviEventDispatcher;
use Civi\Funding\Api4\Action\Helper\AddPermissionsToRecords;
use Civi\Funding\Api4\Action\Traits\FundingActionContactIdRequiredTrait;
use Civi\Funding\Event\FundingProgram\PermissionsGetEvent;

final class GetAction extends DAOGetAction {

  use FundingActionContactIdRequiredTrait;

  private CiviEventDispatcher $_eventDispatcher;

  private AddPermissionsToRecords $_addPermissionsToRecords;

  public function __construct(CiviEventDispatcher $eventDispatcher) {
    parent::__construct(FundingProgram::_getEntityName(), 'get');
    $this->_eventDispatcher = $eventDispatcher;
    $this->_addPermissionsToRecords = new AddPermissionsToRecords(
      fn (array $record) => $this->getRecordPermissions($record)
    );
  }

  public function _run(Result $result): void {
    parent::_run($result);
    ($this->_addPermissionsToRecords)($result);
  }

  /**
   * @param array{id: int} $record
   *
   * @return array<int, string>|null
   */
  private function getRecordPermissions(array $record): ?array {
    $permissionsGetEvent = new PermissionsGetEvent($record['id'], $this->getContactId());
    $this->_eventDispatcher->dispatch(PermissionsGetEvent::class, $permissionsGetEvent);

    return $permissionsGetEvent->getPermissions();
  }

}
