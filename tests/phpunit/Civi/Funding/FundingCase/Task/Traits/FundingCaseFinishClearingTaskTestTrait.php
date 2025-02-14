<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
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

namespace Civi\Funding\FundingCase\Task\Traits;

use Civi\Api4\FundingApplicationProcess;
use Civi\RemoteTools\Api4\Query\Comparison;
use Civi\RemoteTools\Api4\Query\CompositeCondition;

trait FundingCaseFinishClearingTaskTestTrait {

  /**
   * @phpstan-return list<array{array<mixed>, int}>
   */
  protected function createCountEntitiesSeries(
    int $fundingCaseId,
    int $applicationWithUndecidedEligibilityCount,
    int $eligibleApplicationsWithoutFinishedClearingCount
  ): array {
    return [
      // Applications with undecided eligibility.
      [
        [
          FundingApplicationProcess::getEntityName(),
          CompositeCondition::fromFieldValuePairs([
            'funding_case_id' => $fundingCaseId,
            'is_eligible' => NULL,
          ]),
          [],
        ],
        $applicationWithUndecidedEligibilityCount,
      ],
      // Eligible applications without finished clearing.
      [
        [
          FundingApplicationProcess::getEntityName(),
          CompositeCondition::new('AND',
            Comparison::new('funding_case_id', '=', $fundingCaseId),
            Comparison::new('is_eligible', '=', TRUE),
            CompositeCondition::new('OR',
              Comparison::new('clearing_process.id', '=', NULL),
              Comparison::new('clearing_process.status', 'NOT IN', ['accepted', 'rejected']),
            )
          ),
          [
            'join' => [
              [
                'FundingClearingProcess AS clearing_process',
                'LEFT',
                ['clearing_process.application_process_id', '=', 'id'],
              ],
            ],
          ],
        ],
        $eligibleApplicationsWithoutFinishedClearingCount,
      ],
    ];
  }

}
