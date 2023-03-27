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

namespace Civi\Funding\FundingCase;

/**
 * @phpstan-type statusPermissionsActionMapT array<string|null, array<string, array<string>>>
 */
class FundingCaseActionsDeterminer implements FundingCaseActionsDeterminerInterface {

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

  public function getActions(string $status, array $permissions): array {
    return $this->doGetActions($status, $permissions);
  }

  public function isActionAllowed(string $action, string $status, array $permissions): bool {
    return $this->isAnyActionAllowed([$action], $status, $permissions);
  }

  public function isAnyActionAllowed(array $actions, string $status, array $permissions): bool {
    return [] !== array_intersect($this->getActions($status, $permissions), $actions);
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
