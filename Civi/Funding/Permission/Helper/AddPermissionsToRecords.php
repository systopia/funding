<?php
/*
 * Copyright (C) 2023 SYSTOPIA GmbH
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

namespace Civi\Funding\Permission\Helper;

use Civi\Api4\Generic\Result;
use Civi\Funding\Permission\Util\FlattenedPermissionsUtil;

final class AddPermissionsToRecords {

  /**
   * @var callable
   * @phpstan-var callable(array<string, mixed>&array{id: int}): array<string>
   */
  private $getRecordPermissions;

  /**
   * @phpstan-var array<string>
   */
  private array $possiblePermissions;

  /**
   * @phpstan-param array<string> $possiblePermissions
   * @phpstan-param callable(array<string, mixed>&array{id: int}): array<string> $getRecordPermissions
   *   Callable that returns the permissions for a given record. If it returns
   *   NULL, the record is filtered out.
   */
  public function __construct(array $possiblePermissions, callable $getRecordPermissions) {
    $this->possiblePermissions = $possiblePermissions;
    $this->getRecordPermissions = $getRecordPermissions;
  }

  /**
   * @param bool $allowEmptyPermissions
   *   Records without permissions are filtered from result, if not TRUE.
   */
  public function __invoke(Result $result, bool $allowEmptyPermissions = FALSE): void {
    $records = [];
    $filteredRecordsCount = 0;
    /** @phpstan-var array<string, mixed>&array{id: int} $record */
    foreach ($result as $record) {
      $record['permissions'] = $permissions = ($this->getRecordPermissions)($record);
      if ([] === $permissions && !$allowEmptyPermissions) {
        ++$filteredRecordsCount;
        continue;
      }

      FlattenedPermissionsUtil::addFlattenedPermissions($record, $permissions, $this->possiblePermissions);

      $records[] = $record;
    }

    $result->exchangeArray($records);

    if ($filteredRecordsCount > 0) {
      try {
        $result->setCountMatched($result->countMatched() - $filteredRecordsCount);
      }
      // @phpstan-ignore-next-line Exception might be thrown by countMatched().
      catch (\CRM_Core_Exception $e) {
        // row_count was not selected.
        $result->rowCount = count($records);
      }
    }
  }

}
