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

namespace Civi\Funding\ApplicationProcess;

final class ApplicationProcessActionsDeterminer implements ApplicationProcessActionsDeterminerInterface {

  private const STATUS_PERMISSION_ACTIONS_MAP = [
    NULL => [
      'create_application' => ['save'],
      'apply_application' => ['apply'],
    ],
    'new' => [
      'modify_application' => ['save'],
      'apply_application' => ['apply'],
      'delete_application' => ['delete'],
    ],
    'applied' => [
      'modify_application' => ['modify'],
      'withdraw_application' => ['withdraw'],
    ],
    'draft' => [
      'modify_application' => ['save'],
      'apply_application' => ['apply'],
      'withdraw_application' => ['withdraw'],
    ],
  ];

  public function getActions(string $status, array $permissions): array {
    return $this->doGetActions($status, $permissions);
  }

  public function getActionsForNew(array $permissions): array {
    return $this->doGetActions(NULL, $permissions);
  }

  public function isActionAllowed(string $action, string $status, array $permissions): bool {
    return in_array($action, $this->getActions($status, $permissions), TRUE);
  }

  public function isEditAllowed(string $status, array $permissions): bool {
    return $this->isActionAllowed('save', $status, $permissions);
  }

  /**
   * @phpstan-param array<string> $permissions
   *
   * @phpstan-return array<string>
   */
  private function doGetActions(?string $status, array $permissions): array {
    $actions = [];
    foreach ($permissions as $permission) {
      $actions = array_merge($actions, self::STATUS_PERMISSION_ACTIONS_MAP[$status][$permission] ?? []);
    }

    return array_unique($actions);
  }

}
