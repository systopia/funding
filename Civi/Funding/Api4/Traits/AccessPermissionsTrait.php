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

use Civi\Funding\Api4\Permissions;

/**
 * Permissions for funding entities not related to administration that can be
 * read, updated, and deleted. In many cases update or delete doesn't need to be
 * (and shouldn't be) performed directly, but only via specific actions. Read
 * may be necessary for SearchKit. Then use the read only permission trait
 * instead.
 *
 * @see \Civi\Funding\Api4\Traits\AccessROPermissionsTrait
 * @see \Civi\Funding\Api4\Traits\AccessROAdministerRWPermissionsTrait
 */
trait AccessPermissionsTrait {

  /**
   * @return array<string, list<string|list<string>>>
   */
  public static function permissions(): array {
    return [
      'meta' => [
        Permissions::ACCESS_CIVICRM,
        [
          Permissions::ACCESS_FUNDING,
          Permissions::ADMINISTER_FUNDING,
        ],
      ],
      'default' => [
        Permissions::ACCESS_CIVICRM,
        [
          Permissions::ACCESS_FUNDING,
          Permissions::ADMINISTER_FUNDING,
        ],
      ],
    ];
  }

}
