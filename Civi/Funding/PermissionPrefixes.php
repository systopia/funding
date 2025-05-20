<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
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

namespace Civi\Funding;

final class PermissionPrefixes {

  // Note: Check also fundingSelectPermissions.directive.js on change.
  public const APPLICANT = [
    'application_',
    'drawdown_',
    'clearing_',
    'contract_',
  ];

  public static function isApplicantPermission(string $permission): bool {
    foreach (self::APPLICANT as $permissionPrefix) {
      if (\str_starts_with($permission, $permissionPrefix)) {
        return TRUE;
      }
    }

    return FALSE;
  }

}
