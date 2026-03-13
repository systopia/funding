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

namespace Civi\Funding\ApplicationProcess;

use Civi\Funding\Entity\FundingCaseEntity;
use Civi\RemoteTools\Api4\Query\Comparison;
use Civi\RemoteTools\Api4\Query\CompositeCondition;

class EligibleApplicationProcessesLoader {

  private ApplicationProcessManager $applicationProcessManager;

  public function __construct(
    ApplicationProcessManager $applicationProcessManager
  ) {
    $this->applicationProcessManager = $applicationProcessManager;
  }

  /**
   * @phpstan-return array<\Civi\Funding\Entity\ApplicationProcessEntity>
   *
   * @throws \CRM_Core_Exception
   */
  public function getEligibleProcessesForContract(FundingCaseEntity $fundingCase): array {
    return $this->applicationProcessManager->getBy(CompositeCondition::new('AND',
      Comparison::new('funding_case_id', '=', $fundingCase->getId()),
      Comparison::new('amount_eligible', '>', 0)
    ));
  }

}
