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

namespace Civi\Funding\FundingCase\Actions;

use Civi\Funding\Entity\FundingCaseBundle;
use Civi\Funding\Entity\FundingCaseTypeEntity;

interface FundingCaseActionsDeterminerInterface {

  public const SERVICE_TAG = 'funding.case.actions_determiner';

  /**
   * @phpstan-return list<string>
   */
  public static function getSupportedFundingCaseTypes(): array;

  /**
   * @param array<int, \Civi\Funding\Entity\FullApplicationProcessStatus> $applicationProcessStatusList
   *
   * @return list<string>
   */
  public function getActions(FundingCaseBundle $fundingCaseBundle, array $applicationProcessStatusList): array;

  /**
   * @param list<string> $permissions
   *
   * @return list<string>
   */
  public function getInitialActions(FundingCaseTypeEntity $fundingCaseType, array $permissions): array;

  /**
   * @param array<int, \Civi\Funding\Entity\FullApplicationProcessStatus> $applicationProcessStatusList
   */
  public function isActionAllowed(
    string $action,
    FundingCaseBundle $fundingCaseBundle,
    array $applicationProcessStatusList,
  ): bool;

}
