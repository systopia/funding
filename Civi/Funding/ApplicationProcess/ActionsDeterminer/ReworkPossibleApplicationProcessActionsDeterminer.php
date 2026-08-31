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
use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\Entity\FundingCaseTypeEntity;
use Civi\Funding\FundingCase\FundingCaseStatus;
use Civi\Funding\Permission\Traits\HasReviewPermissionTrait;

// phpcs:disable Generic.Files.LineLength.TooLong
final class ReworkPossibleApplicationProcessActionsDeterminer extends AbstractApplicationProcessActionsDeterminer {
// phpcs:enable

  use HasReviewPermissionTrait;

  private const FUNDING_CASE_FINAL_STATUS_LIST = [FundingCaseStatus::CLEARED];

  private const STATUS_PERMISSIONS_ACTION_MAP = [
    'eligible' => [
      'application_request_rework' => ['request-rework', 'add-applicant-comment'],
    ],
    'rework-requested' => [
      'application_apply' => ['add-applicant-comment'],
      'application_modify' => ['add-applicant-comment'],
      'application_request_rework' => ['withdraw-rework-request', 'add-applicant-comment'],
      'review_calculative' => ['approve-rework-request', 'reject-rework-request', 'add-comment'],
      'review_content' => ['approve-rework-request', 'reject-rework-request', 'add-comment'],
      ClearingProcessPermissions::CLEARING_APPLY => ['add-applicant-comment'],
      ClearingProcessPermissions::CLEARING_MODIFY => ['add-applicant-comment'],
    ],
    'rework' => [
      'application_apply' => ['apply', 'add-applicant-comment'],
      'application_modify' => ['save', 'add-applicant-comment'],
      'application_withdraw' => ['withdraw-change'],
      'review_calculative' => ['review', 'add-comment'],
      'review_content' => ['review', 'add-comment'],
      ClearingProcessPermissions::CLEARING_APPLY => ['add-applicant-comment'],
      ClearingProcessPermissions::CLEARING_MODIFY => ['add-applicant-comment'],
    ],
    'rework-review-requested' => [
      'application_apply' => ['add-applicant-comment'],
      'application_modify' => ['request-rework', 'add-applicant-comment'],
      'review_calculative' => ['review', 'add-comment'],
      'review_content' => ['review', 'add-comment'],
      ClearingProcessPermissions::CLEARING_APPLY => ['add-applicant-comment'],
      ClearingProcessPermissions::CLEARING_MODIFY => ['add-applicant-comment'],
    ],
    'rework-review' => [
      'application_apply' => ['add-applicant-comment'],
      'application_modify' => ['add-applicant-comment'],
      'review_calculative' => ['request-change', 'update', 'reject-change', 'add-comment'],
      'review_content' => ['request-change', 'update', 'reject-change', 'add-comment'],
      ClearingProcessPermissions::CLEARING_APPLY => ['add-applicant-comment'],
      ClearingProcessPermissions::CLEARING_MODIFY => ['add-applicant-comment'],
    ],
  ];

  private ApplicationProcessActionsDeterminerInterface $actionsDeterminer;

  private DetermineApproveRejectActionsHelper $determineApproveRejectActionsHelper;

  public function __construct(ApplicationProcessActionsDeterminerInterface $actionsDeterminer) {
    $this->actionsDeterminer = $actionsDeterminer;
    $this->determineApproveRejectActionsHelper = new DetermineApproveRejectActionsHelper(
      ['rework-review'],
      ['approve' => 'approve-change']
    );
    parent::__construct(self::STATUS_PERMISSIONS_ACTION_MAP);
  }

  public function getActions(ApplicationProcessEntityBundle $applicationProcessBundle, array $statusList): array {
    if ($applicationProcessBundle->getFundingCase()->isStatusIn(self::FUNDING_CASE_FINAL_STATUS_LIST)) {
      return [];
    }

    $permissions = $applicationProcessBundle->getFundingCase()->getPermissions();

    return \array_values(\array_unique(\array_merge(
      parent::getActions($applicationProcessBundle, $statusList),
      $this->actionsDeterminer->getActions($applicationProcessBundle, $statusList),
      $this->determineApproveRejectActionsHelper->getActions(
        $applicationProcessBundle->getApplicationProcess()->getFullStatus(),
        $this->hasReviewCalculativePermission($permissions),
        $this->hasReviewContentPermission($permissions)
      ),
    )));
  }

  public function getInitialActions(
    array $permissions,
    FundingCaseTypeEntity $fundingCaseType,
    ?FundingCaseEntity $fundingCase
  ): array {
    return \array_values(\array_unique(\array_merge(
      parent::getInitialActions($permissions, $fundingCaseType, $fundingCase),
      $this->actionsDeterminer->getInitialActions($permissions, $fundingCaseType, $fundingCase),
    )));
  }

}
