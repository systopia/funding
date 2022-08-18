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

interface PermissionCheckerInterface {

  /**
   * Checks if the available permissions match the required ones. It does
   * actually the same as \CRM_Core_Permission::check(), but does not depend on
   * a contact's permissions.
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
   *
   * @see \CRM_Core_Permission::check()
   */
  public function isAccessGranted(array $requiredPermissions, array $availablePermissions): bool;

}
