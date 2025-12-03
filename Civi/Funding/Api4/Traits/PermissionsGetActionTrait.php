<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
 *
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License as published by the Free
 * Software Foundation, either version 3 of the License, or (at your option) any
 * later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types = 1);

namespace Civi\Funding\Api4\Traits;

use Civi\Api4\Generic\Result;
use Civi\Api4\Generic\Traits\ArrayQueryActionTrait;
use Civi\Funding\Api4\Action\Traits\IsFieldSelectedTrait;
use Civi\Funding\Permission\Helper\AddPermissionsToRecords;

/**
 * Adds permissions to records in GetAction.
 *
 * To make pagination possible all records are loaded and offset and limit are
 * applied afterwords. For this reason this trait should only be used in cases
 * where this operation doesn't take much time.
 */
trait PermissionsGetActionTrait {

  use ArrayQueryActionTrait;

  use IsFieldSelectedTrait;

  private AddPermissionsToRecords $_addPermissionsToRecords;

  public function _run(Result $result): void {
    $limit = $this->getLimit();
    $offset = $this->getOffset();
    $select = $this->getSelect();

    foreach ($this->getFieldsRequiredToGetPermissions() as $field) {
      if (!$this->isFieldSelected($field)) {
        $this->addSelect($field);
      }
    }

    // We initially select all records (if records without permissions are
    // filtered) so pagination is possible.
    if (!$this->isAllowEmptyRecordPermissions()) {
      $this->setLimit(0);
      $this->setOffset(0);
    }

    parent::_run($result);
    $this->_addPermissionsToRecords ??= new AddPermissionsToRecords(
      $this->getPossiblePermissions(),
      fn (array $record) => $this->getRecordPermissions($record)
    );
    ($this->_addPermissionsToRecords)($result, $this->isAllowEmptyRecordPermissions());

    $this->setLimit($limit);
    $this->setOffset($offset);

    $records = $result->getArrayCopy();
    if (!$this->isAllowEmptyRecordPermissions()) {
      $records = $this->limitArray($records);
    }
    if ($this->getSelect() !== $select && !in_array('*', $select, TRUE)) {
      $this->setSelect($select);
      $records = $this->selectArray($records);
    }

    $result->exchangeArray($records);
  }

  /**
   * @phpstan-param array{id: int} $record
   *
   * @phpstan-return list<string>
   */
  abstract protected function getRecordPermissions(array $record): array;

  /**
   * @phpstan-return list<string>
   */
  abstract protected function getPossiblePermissions(): array;

  /**
   * @phpstan-return array<string>
   *   Name of the fields used to retrieve the permissions.
   */
  protected function getFieldsRequiredToGetPermissions(): array {
    return ['id'];
  }

  /**
   * @return bool
   *   Records without permissions are filtered from result, if not TRUE.
   */
  protected function isAllowEmptyRecordPermissions(): bool {
    return FALSE;
  }

}
