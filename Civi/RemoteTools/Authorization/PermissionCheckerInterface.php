<?php
declare(strict_types = 1);

namespace Civi\RemoteTools\Authorization;

interface PermissionCheckerInterface {

  /**
   * Checks if the available permissions match the required ones.
   *
   * Examples for required permissions:
   *
   * Ex 1: Must have 'access CiviCRM'
   *    ['access CiviCRM']
   *
   *  Ex 2: Must have 'access CiviCRM' and 'access AJAX API'
   *    ['access CiviCRM', 'access AJAX API']
   *
   * Ex 3: Must have 'access CiviCRM' or 'access AJAX API'
   *   [
   *     ['access CiviCRM', 'access AJAX API'],
   *   ],
   *
   * Ex 4: Must have 'access CiviCRM' or 'access AJAX API' AND 'access CiviEvent'
   *   [
   *     ['access CiviCRM', 'access AJAX API'],
   *     'access CiviEvent',
   *   ],
   *
   * @param array<string[]|string> $requiredPermissions All permissions on the
   *   first level must be matched (AND). For permissions on the second level
   *   only one needs to be matched (OR).
   * @param string[] $availablePermissions
   *
   * @return bool
   */
  public function isAccessGranted(array $requiredPermissions, array $availablePermissions): bool;

}
