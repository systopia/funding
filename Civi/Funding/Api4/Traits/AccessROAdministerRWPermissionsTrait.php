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

namespace Civi\Funding\Api4\Traits;

/**
 * Permissions for funding entities that may only be changed with administer
 * permissions, but are allowed to be read without.
 */
trait AccessROAdministerRWPermissionsTrait {

  /**
   * @return array<string, array<string|string[]>>
   */
  public static function permissions(): array {
    $accessPermissions = AccessPermissionsTrait::permissions();
    $administerPermissions = AdministerPermissionsTrait::permissions();

    return [
      'meta' => $accessPermissions['meta'],
      'default' => $administerPermissions['default'],
      'get' => $accessPermissions['default'],
    ];
  }

}
