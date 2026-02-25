<?php
/*
 * Copyright (C) 2023 SYSTOPIA GmbH
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

namespace Civi\Funding\Form\Application;

use Civi\Funding\Entity\ApplicationProcessEntityBundle;
use Civi\Funding\Entity\FundingCaseTypeEntity;

interface ApplicationSubmitActionsFactoryInterface {

  /**
   * @param list<string> $permissions
   *
   * @return array<string, \Civi\Funding\FundingCaseType\MetaData\ApplicationProcessAction>
   *   Map of action names to actions.
   */
  public function getInitialSubmitActions(array $permissions, FundingCaseTypeEntity $fundingCaseType): array;

  /**
   * @param array<int, \Civi\Funding\Entity\FullApplicationProcessStatus> $statusList
   *     Status of other application processes in same funding case indexed by ID.
   *
   * @return array<string, \Civi\Funding\FundingCaseType\MetaData\ApplicationProcessAction>
   *   Map of action names to actions.
   */
  public function getSubmitActions(
    ApplicationProcessEntityBundle $applicationProcessBundle,
    array $statusList
  ): array;

}
