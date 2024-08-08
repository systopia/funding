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

use Civi\Funding\Entity\ApplicationProcessEntityBundle;

interface ApplicationProcessActionsDeterminerInterface {

  public const SERVICE_TAG = 'funding.application.actions_determiner';

  /**
   * @phpstan-return list<string>
   */
  public static function getSupportedFundingCaseTypes(): array;

  /**
   * @phpstan-param array<int, \Civi\Funding\Entity\FullApplicationProcessStatus> $statusList
   *    Status of other application processes in same funding case indexed by ID.
   *
   * @phpstan-return list<string>
   */
  public function getActions(ApplicationProcessEntityBundle $applicationProcessBundle, array $statusList): array;

  /**
   * @phpstan-param array<string> $permissions
   *
   * @phpstan-return array<string>
   */
  public function getInitialActions(array $permissions): array;

  /**
   * @phpstan-param array<int, \Civi\Funding\Entity\FullApplicationProcessStatus> $statusList
   *     Status of other application processes in same funding case indexed by ID.
   */
  public function isActionAllowed(
    string $action,
    ApplicationProcessEntityBundle $applicationProcessBundle,
    array $statusList
  ): bool;

  /**
   * @phpstan-param array<string> $actions
   * @phpstan-param array<int, \Civi\Funding\Entity\FullApplicationProcessStatus> $statusList
   *     Status of other application processes in same funding case indexed by ID.
   *
   * @return bool
   *   true if one of the specified actions is allowed.
   */
  public function isAnyActionAllowed(
    array $actions,
    ApplicationProcessEntityBundle $applicationProcessBundle,
    array $statusList
  ): bool;

  /**
   * @phpstan-param array<int, \Civi\Funding\Entity\FullApplicationProcessStatus> $statusList
   *     Status of other application processes in same funding case indexed by ID.
   *
   * @return bool
   *   true if an action that allows to edit the application details is
   *   available.
   */
  public function isEditAllowed(
    ApplicationProcessEntityBundle $applicationProcessBundle,
    array $statusList
  ): bool;

}
