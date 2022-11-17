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

namespace Civi\Funding\ApplicationProcess\ActionsDeterminer;

use Civi\Funding\Entity\FullApplicationProcessStatus;

/**
 * @phpstan-type statusPermissionsActionMapT array<string|null, array<string, array<string>>>
 */
class ApplicationProcessActionsDeterminer implements ApplicationProcessActionsDeterminerInterface {

  /**
   * @phpstan-var statusPermissionsActionMapT
   */
  private array $statusPermissionActionsMap;

  /**
   * @phpstan-param statusPermissionsActionMapT $statusPermissionActionsMap
   */
  public function __construct(array $statusPermissionActionsMap) {
    $this->statusPermissionActionsMap = $statusPermissionActionsMap;
  }

  public function getActions(FullApplicationProcessStatus $status, array $permissions): array {
    return $this->doGetActions($status->getStatus(), $permissions);
  }

  public function getInitialActions(array $permissions): array {
    return $this->doGetActions(NULL, $permissions);
  }

  public function isActionAllowed(string $action, FullApplicationProcessStatus $status, array $permissions): bool {
    return \in_array($action, $this->getActions($status, $permissions), TRUE);
  }

  public function isEditAllowed(FullApplicationProcessStatus $status, array $permissions): bool {
    return $this->isActionAllowed('save', $status, $permissions)
      || $this->isActionAllowed('apply', $status, $permissions);
  }

  /**
   * @phpstan-param array<string> $permissions
   *
   * @phpstan-return array<string>
   */
  private function doGetActions(?string $status, array $permissions): array {
    $actions = [];
    foreach ($permissions as $permission) {
      $actions = \array_merge($actions, $this->statusPermissionActionsMap[$status][$permission] ?? []);
    }

    return \array_unique($actions);
  }

}
