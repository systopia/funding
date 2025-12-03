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

namespace Civi\Funding\Permission\Util;

use Civi\Funding\Api4\RemoteApiConstants;

final class FlattenedPermissionsUtil {

  /**
   * Adds flattened permissions to the given record. Flattened permissions might
   * be useful for some frontends (e.g. Drupal Views).
   *
   * @phpstan-param array<string, mixed> $record
   * @phpstan-param array<string> $permissions Subset of $possiblePermissions.
   * @phpstan-param array<string> $possiblePermissions
   */
  public static function addFlattenedPermissions(array &$record, array $permissions, array $possiblePermissions): void {
    foreach ($possiblePermissions as $permission) {
      $record[RemoteApiConstants::PERMISSION_FIELD_PREFIX . $permission] = FALSE;
    }
    foreach ($permissions as $permission) {
      $record[RemoteApiConstants::PERMISSION_FIELD_PREFIX . $permission] = TRUE;
    }
  }

}
