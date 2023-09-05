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

namespace Civi\Funding\FundingProgram;

use Civi\Api4\FundingCaseTypeProgram;
use Civi\RemoteTools\Api4\Api4Interface;

/**
 * @codeCoverageIgnore
 */
class FundingCaseTypeProgramRelationChecker {

  private Api4Interface $api4;

  public function __construct(Api4Interface $api4) {
    $this->api4 = $api4;
  }

  public function areFundingCaseTypeAndProgramRelated(int $fundingCaseTypeId, int $fundingProgramId): bool {
    $action = FundingCaseTypeProgram::getRelation(FALSE)
      ->setFundingCaseTypeId($fundingCaseTypeId)
      ->setFundingProgramId($fundingProgramId);

    return $this->api4->executeAction($action)->rowCount === 1;
  }

}
