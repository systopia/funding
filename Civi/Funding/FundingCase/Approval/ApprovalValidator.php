<?php
/*
 * Copyright (C) 2026 SYSTOPIA GmbH
 *
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License as published by the Free
 * Software Foundation, either version 3 of the License, or (at your option) any
 * later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types = 1);

namespace Civi\Funding\FundingCase\Approval;

use Civi\Funding\ApplicationProcess\EligibleApplicationProcessesLoader;
use Civi\Funding\Entity\FundingCaseBundle;

class ApprovalValidator {

  private EligibleApplicationProcessesLoader $eligibleApplicationProcessesLoader;

  public function __construct(EligibleApplicationProcessesLoader $eligibleApplicationProcessesLoader) {
    $this->eligibleApplicationProcessesLoader = $eligibleApplicationProcessesLoader;
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function isAmountAllowed(
    float $amount,
    FundingCaseBundle $fundingCaseBundle
  ): bool {
    if (TRUE !== $fundingCaseBundle->getFundingCaseType()->getProperty('amountApprovedNonAdjustable')) {
      return TRUE;
    }

    $eligibleApplicationProcesses = $this->eligibleApplicationProcessesLoader->getEligibleProcessesForContract(
      $fundingCaseBundle->getFundingCase()
    );
    $amountRequestedEligible = 0;
    foreach ($eligibleApplicationProcesses as $eligibleApplicationProcess) {
      $amountRequestedEligible += $eligibleApplicationProcess->getAmountRequested();
    }

    return abs($amount - $amountRequestedEligible) <= PHP_FLOAT_EPSILON;
  }

}
