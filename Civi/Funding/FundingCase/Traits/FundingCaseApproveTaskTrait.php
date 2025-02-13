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

namespace Civi\Funding\FundingCase\Traits;

use Civi\Api4\FundingApplicationProcess;
use Civi\Funding\Entity\FundingCaseBundle;
use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\Api4\Query\CompositeCondition;

trait FundingCaseApproveTaskTrait {

  protected static string $taskType = 'approve';

  protected Api4Interface $api4;

  /**
   * @phpstan-return list<string>
   */
  abstract public static function getSupportedFundingCaseTypes(): array;

  public function __construct(Api4Interface $api4) {
    $this->api4 = $api4;
  }

  /**
   * @throws \CRM_Core_Exception
   */
  protected function existsApplicationWithUndecidedEligibility(FundingCaseBundle $fundingCaseBundle): bool {
    return $this->api4->countEntities(
        FundingApplicationProcess::getEntityName(),
        CompositeCondition::fromFieldValuePairs([
          'funding_case_id' => $fundingCaseBundle->getFundingCase()->getId(),
          'is_eligible' => NULL,
        ])
      ) > 0;
  }

}
