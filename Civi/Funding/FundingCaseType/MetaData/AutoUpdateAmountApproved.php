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

namespace Civi\Funding\FundingCaseType\MetaData;

enum AutoUpdateAmountApproved {

  case No;

  /**
   * If the amount eligible of an application process is changed, the amount
   * approved of the corresponding funding case will be automatically
   * increased/decreased accordingly.
   */
  case OnAmountEligibleChange;

  /**
   * If the amount eligible or the eligibility flag of an application process
   * is changed, the amount approved of the corresponding funding case will be
   * automatically changed to the sum of the amounts approved of all application
   * processes in the funding case. Precondition: The funding case actions
   * determiner allows the action FundingCaseActions::UPDATE_AMOUNT_APPROVED
   * with the permission FundingCasePermissions::AUTO_UPDATE_AMOUNT_APPROVED.
   * The actions determiner might disallow the automatic change depending on
   * the application processes statuses.
   *
   * @see \Civi\Funding\FundingCase\Actions\FundingCaseActions::UPDATE_AMOUNT_APPROVED
   * @see \Civi\Funding\FundingCase\FundingCasePermissions::AUTO_UPDATE_AMOUNT_APPROVED
   */
  case SumOfAmountsEligible;
}
