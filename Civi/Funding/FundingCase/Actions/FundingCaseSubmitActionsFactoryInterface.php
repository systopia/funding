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

interface FundingCaseSubmitActionsFactoryInterface {

  /**
   * @param list<string> $permissions
   *
   * @return array<string, \Civi\Funding\FundingCaseType\MetaData\FundingCaseAction>
   */
  public function getInitialSubmitActions(array $permissions, FundingCaseTypeEntity $fundingCaseType): array;

  /**
   * @param array<int, \Civi\Funding\Entity\FullApplicationProcessStatus> $applicationProcessStatusList
   *
   * @return array<string, \Civi\Funding\FundingCaseType\MetaData\FundingCaseAction>
   */
  public function getSubmitActions(FundingCaseBundle $fundingCaseBundle, array $applicationProcessStatusList): array;

}
