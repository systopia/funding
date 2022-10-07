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

interface ApplicationProcessActionsDeterminerInterface {

  /**
   * @phpstan-param array<string> $permissions
   *
   * @phpstan-return array<string>
   */
  public function getActions(string $status, array $permissions): array;

  /**
   * @phpstan-param array<string> $permissions
   *
   * @phpstan-return array<string>
   */
  public function getActionsForNew(array $permissions): array;

  /**
   * @phpstan-param array<string> $permissions
   */
  public function isActionAllowed(string $action, string $status, array $permissions): bool;

  /**
   * @phpstan-param array<string> $permissions
   *
   * @return bool
   *   true if an action that allows to edit the application details is
   *   available.
   */
  public function isEditAllowed(string $status, array $permissions): bool;

}
