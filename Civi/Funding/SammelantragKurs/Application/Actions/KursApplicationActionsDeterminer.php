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

namespace Civi\Funding\SammelantragKurs\Application\Actions;

use Civi\Funding\ApplicationProcess\ActionsDeterminer\ApplicationProcessActionsDeterminer;
use Civi\Funding\Entity\FullApplicationProcessStatus;
use Civi\Funding\Permission\Traits\HasReviewPermissionTrait;
use Civi\Funding\SammelantragKurs\Traits\KursSupportedFundingCaseTypesTrait;

final class KursApplicationActionsDeterminer extends ApplicationProcessActionsDeterminer {

  use HasReviewPermissionTrait;

  use KursSupportedFundingCaseTypesTrait;

  private const STATUS_PERMISSION_ACTIONS_MAP = [
    NULL => [
      'application_create' => ['save', 'save&new', 'save&copy'],
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
      'application_modify' => ['modify'],
      'review_calculative' => ['update', 'add-comment'],
      'review_content' => ['update', 'add-comment'],
    ],
    'rework' => [
      'application_apply' => ['apply'],
      'application_modify' => ['save'],
      'application_withdraw' => ['withdraw-change'],
      'review_calculative' => ['review', 'add-comment'],
      'review_content' => ['review', 'add-comment'],
    ],
    'rework-review-requested' => [
      'application_modify' => ['modify'],
      'review_calculative' => ['review', 'add-comment'],
      'review_content' => ['review', 'add-comment'],
    ],
    'rework-review' => [
      'review_calculative' => ['request-change', 'update', 'reject-change', 'add-comment'],
      'review_content' => ['request-change', 'update', 'reject-change', 'add-comment'],
    ],
  ];

  private KursApplicationActionStatusInfo $statusInfo;

  public function __construct(KursApplicationActionStatusInfo $statusInfo) {
    parent::__construct(self::STATUS_PERMISSION_ACTIONS_MAP);
    $this->statusInfo = $statusInfo;
  }

  public function getActions(FullApplicationProcessStatus $status, array $statusList, array $permissions): array {
    if (!$this->hasReviewPermission($permissions) && $this->isAnyApplicationInReview($statusList)) {
      return [];
    }

    return parent::getActions($status, $statusList, $permissions);
  }

  /**
   * @phpstan-param array<int, FullApplicationProcessStatus> $statusList
   */
  private function isAnyApplicationInReview(array $statusList): bool {
    foreach ($statusList as $status) {
      if ($this->statusInfo->isReviewStatus($status->getStatus())) {
        return TRUE;
      }
    }

    return FALSE;
  }

}
