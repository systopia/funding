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

use Civi\Funding\ApplicationProcess\ApplicationProcessManager;
use Civi\Funding\Entity\FundingCaseBundle;
use Civi\Funding\Util\FloatUtil;

class ApprovalValidator {

  private ApplicationProcessManager $applicationProcessManager;

  public function __construct(ApplicationProcessManager $applicationProcessManager) {
    $this->applicationProcessManager = $applicationProcessManager;
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

    $amountEligible = $this->applicationProcessManager->getAmountEligibleByFundingCaseId(
      $fundingCaseBundle->getFundingCase()->getId(),
    );

    return FloatUtil::isMoneyEqual($amount, $amountEligible);
  }

}
