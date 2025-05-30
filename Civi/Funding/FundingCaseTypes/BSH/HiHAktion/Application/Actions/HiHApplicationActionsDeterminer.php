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

namespace Civi\Funding\FundingCaseTypes\BSH\HiHAktion\Application\Actions;

use Civi\Funding\ApplicationProcess\ActionsDeterminer\AbstractApplicationProcessActionsDeterminer;
use Civi\Funding\Entity\ApplicationProcessEntityBundle;
use Civi\Funding\FundingCase\FundingCaseStatus;
use Civi\Funding\FundingCaseTypes\BSH\HiHAktion\Traits\HiHSupportedFundingCaseTypesTrait;

final class HiHApplicationActionsDeterminer extends AbstractApplicationProcessActionsDeterminer {

  use HiHSupportedFundingCaseTypesTrait;

  private const FUNDING_CASE_FINAL_STATUS_LIST = [FundingCaseStatus::CLEARED];

  private const STATUS_PERMISSION_ACTIONS_MAP = [
    NULL => [
      'application_create' => ['save'],
      'application_apply' => ['apply'],
    ],
    'new' => [
      'application_modify' => ['save'],
      'application_apply' => ['apply'],
      'application_withdraw' => ['withdraw'],
    ],
    'applied' => [
      'application_modify' => ['modify'],
      'application_withdraw' => ['withdraw'],
      'review_application' => ['review', 'add-comment'],
    ],
    'review' => [
      'review_application' => ['request-change', 'reject', 'release', 'add-comment'],
      'bsh_admin' => ['update'],
    ],
    'draft' => [
      'application_modify' => ['save'],
      'application_apply' => ['apply'],
      'application_withdraw' => ['withdraw'],
      'review_application' => ['add-comment'],
    ],
    'advisory' => [
      'review_application' => ['add-comment'],
      'bsh_admin' => ['update', 're-apply', 'reject', 'approve'],
    ],
    'approved' => [
      'bsh_admin' => ['update', 'approve-update', 'recall'],
      'review_application' => ['add-comment'],
    ],
    'approved_partial' => [
      'bsh_admin' => ['update', 'approve-update', 'recall'],
      'review_application' => ['add-comment'],
    ],
    'rejected' => [
      'bsh_admin' => ['re-apply'],
      'review_application' => ['add-comment'],
    ],
    'rejected_after_advisory' => [
      'bsh_admin' => ['re-release'],
      'review_application' => ['add-comment'],
    ],
    'complete' => [
      'review_application' => ['add-comment', 'approve-update'],
    ],
  ];

  public function __construct() {
    parent::__construct(self::STATUS_PERMISSION_ACTIONS_MAP);
  }

  public function getActions(ApplicationProcessEntityBundle $applicationProcessBundle, array $statusList): array {
    if ($applicationProcessBundle->getFundingCase()->isStatusIn(self::FUNDING_CASE_FINAL_STATUS_LIST)) {
      return [];
    }

    return parent::getActions($applicationProcessBundle, $statusList);
  }

}
