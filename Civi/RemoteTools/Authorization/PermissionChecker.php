<?php
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
