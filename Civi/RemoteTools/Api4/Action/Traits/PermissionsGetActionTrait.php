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

use Civi\Api4\Generic\Result;
use Civi\RemoteTools\Api4\Action\Helper\AddPermissionsToRecords;

/**
 * Adds permissions to records in GetAction.
 */
trait PermissionsGetActionTrait {

  private AddPermissionsToRecords $_addPermissionsToRecords;

  public function _run(Result $result): void {
    parent::_run($result);
    $this->_addPermissionsToRecords ??= new AddPermissionsToRecords(
      $this->getPossiblePermissions(),
      fn (array $record) => $this->getRecordPermissions($record)
    );
    ($this->_addPermissionsToRecords)($result);
  }

  /**
   * @param array{id: int} $record
   *
   * @return array<int, string>|null
   */
  abstract protected function getRecordPermissions(array $record): ?array;

  /**
   * @phpstan-return array<string>
   */
  abstract protected function getPossiblePermissions(): array;

}
