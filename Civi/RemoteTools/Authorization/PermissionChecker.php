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

namespace Civi\RemoteTools\Authorization;

final class PermissionChecker implements PermissionCheckerInterface {

  public function isAccessGranted(array $requiredPermissions, array $availablePermissions): bool {
    // based on code from \CRM_Core_Permission::check()
    foreach ($requiredPermissions as $permission) {
      if (is_array($permission)) {
        foreach ($permission as $orPerm) {
          if ($this->isAccessGranted((array) $orPerm, $availablePermissions)) {
            // one of our 'or' permissions has succeeded - stop checking this permission
            return TRUE;
          }
        }
        // none of our conditions was met
        return FALSE;
      }
      else {
        // This is an individual permission
        $impliedPermissions = \CRM_Core_Permission::getImpliedPermissionsFor($permission);
        $impliedPermissions[] = $permission;
        foreach ($impliedPermissions as $permissionOption) {
          $granted = in_array($permissionOption, $availablePermissions, TRUE);
          if ($granted) {
            break;
          }
        }

        if (!$granted) {
          // one of our 'and' conditions has not been met
          return FALSE;
        }
      }
    }

    return TRUE;
  }

}
