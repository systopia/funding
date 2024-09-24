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
use Civi\Funding\Entity\ApplicationProcessEntityBundle;
use Civi\Funding\FundingCase\FundingCaseStatus;
use Civi\Funding\Permission\Traits\HasReviewPermissionTrait;

final class DefaultApplicationProcessActionsDeterminer extends AbstractApplicationProcessActionsDeterminer {

  /**
   * @inheritDoc
   */
  public static function getSupportedFundingCaseTypes(): array {
    return [];
  }

  use HasReviewPermissionTrait;

  private const FUNDING_CASE_FINAL_STATUS_LIST = [FundingCaseStatus::CLEARED];

  private const STATUS_PERMISSION_ACTIONS_MAP = [
    NULL => [
      'application_create' => ['save'],
      'application_apply' => ['apply'],
    ],
    'new' => [
      'application_modify' => ['save'],
      'application_apply' => ['apply'],
      'application_withdraw' => ['delete'],
    ],
    'applied' => [
      'application_modify' => ['modify'],
      'application_withdraw' => ['withdraw'],
      'review_calculative' => ['review', 'add-comment'],
      'review_content' => ['review', 'add-comment'],
    ],
    'review' => [
      'review_calculative' => ['request-change', 'update', 'reject', 'add-comment'],
      'review_content' => ['request-change', 'update', 'reject', 'add-comment'],
    ],
    'draft' => [
      'application_modify' => ['save'],
      'application_apply' => ['apply'],
      'application_withdraw' => ['withdraw'],
      'review_calculative' => ['review', 'add-comment'],
      'review_content' => ['review', 'add-comment'],
    ],
    'eligible' => [
      'application_withdraw' => ['withdraw'],
      'review_calculative' => ['update', 'add-comment'],
      'review_content' => ['update', 'add-comment'],
    ],
    'complete' => [
      'application_withdraw' => ['withdraw'],
      'review_calculative' => ['update', 'add-comment'],
      'review_content' => ['update', 'add-comment'],
    ],
  ];

  private DetermineApproveRejectActionsHelper $determineApproveRejectActionsHelper;

  public function __construct() {
    parent::__construct(self::STATUS_PERMISSION_ACTIONS_MAP);
    $this->determineApproveRejectActionsHelper = new DetermineApproveRejectActionsHelper();
  }

  public function getActions(ApplicationProcessEntityBundle $applicationProcessBundle, array $statusList): array {
    if (in_array(
      $applicationProcessBundle->getFundingCase()->getStatus(),
      self::FUNDING_CASE_FINAL_STATUS_LIST,
      TRUE
    )) {
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
