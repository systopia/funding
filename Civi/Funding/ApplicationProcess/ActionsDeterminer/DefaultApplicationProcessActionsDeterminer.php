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

namespace Civi\Funding\ApplicationProcess\ActionsDeterminer;

use Civi\Funding\ApplicationProcess\ActionsDeterminer\Helper\DetermineApproveRejectActionsHelper;
use Civi\Funding\ClearingProcess\ClearingProcessPermissions;
use Civi\Funding\Entity\ApplicationProcessEntityBundle;
use Civi\Funding\FundingCase\FundingCaseStatus;
use Civi\Funding\Permission\Traits\HasReviewPermissionTrait;

final class DefaultApplicationProcessActionsDeterminer extends AbstractApplicationProcessActionsDeterminer {

  use HasReviewPermissionTrait;

  private const FUNDING_CASE_FINAL_STATUS_LIST = [FundingCaseStatus::CLEARED];

  private const STATUS_PERMISSION_ACTIONS_MAP = [
    NULL => [
      'application_create' => ['save'],
      'application_apply' => ['apply'],
    ],
    'new' => [
      'application_modify' => ['save', 'add-applicant-comment'],
      'application_apply' => ['apply', 'add-applicant-comment'],
      'application_withdraw' => ['delete'],
    ],
    'applied' => [
      'application_apply' => ['add-applicant-comment'],
      'application_modify' => ['modify', 'add-applicant-comment'],
      'application_withdraw' => ['withdraw'],
      'review_calculative' => ['review', 'add-comment'],
      'review_content' => ['review', 'add-comment'],
    ],
    'open&applied' => [
      'review_calculative' => ['move-to-new-funding-case'],
      'review_content' => ['move-to-new-funding-case'],
    ],
    'review' => [
      'application_apply' => ['add-applicant-comment'],
      'application_modify' => ['add-applicant-comment'],
      'review_calculative' => ['request-change', 'update', 'reject', 'add-comment'],
      'review_content' => ['request-change', 'update', 'reject', 'add-comment'],
    ],
    'open&review' => [
      'review_calculative' => ['move-to-new-funding-case'],
      'review_content' => ['move-to-new-funding-case'],
    ],
    'draft' => [
      'application_modify' => ['save', 'add-applicant-comment'],
      'application_apply' => ['apply', 'add-applicant-comment'],
      'application_withdraw' => ['withdraw'],
      'review_calculative' => ['review', 'add-comment'],
      'review_content' => ['review', 'add-comment'],
    ],
    'eligible' => [
      'application_apply' => ['add-applicant-comment'],
      'application_modify' => ['add-applicant-comment'],
      'application_withdraw' => ['withdraw'],
      'review_calculative' => ['update', 'add-comment'],
      'review_content' => ['update', 'add-comment'],
      ClearingProcessPermissions::CLEARING_APPLY => ['add-applicant-comment'],
      ClearingProcessPermissions::CLEARING_MODIFY => ['add-applicant-comment'],
    ],
    'open&eligible' => [
      'review_calculative' => ['move-to-new-funding-case'],
      'review_content' => ['move-to-new-funding-case'],
    ],
    'complete' => [
      'application_apply' => ['add-applicant-comment'],
      'application_modify' => ['add-applicant-comment'],
      'application_withdraw' => ['withdraw'],
      'review_calculative' => ['update', 'add-comment'],
      'review_content' => ['update', 'add-comment'],
      ClearingProcessPermissions::CLEARING_APPLY => ['add-applicant-comment'],
      ClearingProcessPermissions::CLEARING_MODIFY => ['add-applicant-comment'],
    ],
  ];

  private DetermineApproveRejectActionsHelper $determineApproveRejectActionsHelper;

  public function __construct() {
    parent::__construct(self::STATUS_PERMISSION_ACTIONS_MAP);
    $this->determineApproveRejectActionsHelper = new DetermineApproveRejectActionsHelper();
  }

  public function getActions(ApplicationProcessEntityBundle $applicationProcessBundle, array $statusList): array {
    if ($applicationProcessBundle->getFundingCase()->isStatusIn(self::FUNDING_CASE_FINAL_STATUS_LIST)) {
      return [];
    }

    $permissions = $applicationProcessBundle->getFundingCase()->getPermissions();

    return array_merge(
      parent::getActions($applicationProcessBundle, $statusList),
      $this->determineApproveRejectActionsHelper->getActions(
        $applicationProcessBundle->getApplicationProcess()->getFullStatus(),
        $this->hasReviewCalculativePermission($permissions),
        $this->hasReviewContentPermission($permissions)
      ),
    );
  }

}
