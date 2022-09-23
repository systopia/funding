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
    $additionallySelectedFields = [];
    $countSelectedOnly = $this->isRowCountSelectedOnly();
    if ($countSelectedOnly) {
      $this->addSelect(...$this->getFieldsRequiredToGetPermissions());
    }
    else {
      foreach ($this->getFieldsRequiredToGetPermissions() as $field) {
        if (!$this->isFieldSelected($field)) {
          $additionallySelectedFields[] = $field;
          $this->addSelect($field);
        }
      }
    }

    parent::_run($result);
    $this->_addPermissionsToRecords ??= new AddPermissionsToRecords(
      $this->getPossiblePermissions(),
      fn (array $record) => $this->getRecordPermissions($record)
    );
    ($this->_addPermissionsToRecords)($result);

    if ($countSelectedOnly) {
      $result->setCountMatched($result->count());
      $result->exchangeArray([]);
    }
    elseif ([] !== $additionallySelectedFields) {
      /** @var array<string, mixed> $record */
      foreach ($result as &$record) {
        foreach ($additionallySelectedFields as $field) {
          unset($record[$field]);
        }
      }
    }
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

  /**
   * @phpstan-return array<string>
   *   Name of the fields used to retrieve the permissions.
   */
  protected function getFieldsRequiredToGetPermissions(): array {
    return ['id'];
  }

  private function isFieldSelected(string $field): bool {
    $select = $this->getSelect();

    return [] === $select
      || \in_array('*', $select, TRUE)
      || \in_array($field, $select, TRUE);
  }

  private function isRowCountSelectedOnly(): bool {
    return ['row_count'] === $this->getSelect();
  }

}
