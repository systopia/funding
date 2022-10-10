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

use CRM_Funding_ExtensionUtil as E;

final class ApplicationProcessActionsDeterminer {

  /**
   * @phpstan-param array<string> $permissions
   *
   * @phpstan-return array<string, string>
   *   Action mapped to label.
   */
  public function getActions(string $status, array $permissions): array {
    $actions = [];
    if (in_array('modify_application', $permissions, TRUE)) {
      $actions['save'] = E::ts('Save');
    }
    if (in_array('apply_application', $permissions, TRUE)) {
      $actions['apply'] = E::ts('Apply');
    }

    return $actions;
  }

  /**
   * @phpstan-param array<string> $permissions
   *
   * @phpstan-return array<string, string>
   *   Action mapped to label.
   */
  public function getActionsForNew(array $permissions): array {
    $actions = [];
    if (in_array('create_application', $permissions, TRUE)) {
      $actions['save'] = E::ts('Save');
    }
    if (in_array('apply_application', $permissions, TRUE)) {
      $actions['apply'] = E::ts('Apply');
    }

    return $actions;
  }

  /**
   * @phpstan-param array<string> $permissions
   */
  public function isModifyAllowed(string $status, array $permissions): bool {
    return in_array('modify_application', $permissions, TRUE);
  }

}
