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

namespace Civi\Funding\FundingCaseTypes\AdB\SammelantragKurs\Application\Actions;

use Civi\Funding\ApplicationProcess\ActionsDeterminer\ApplicationProcessActionsDeterminer;
use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\Entity\FundingCaseTypeEntity;
use Civi\Funding\FundingCase\FundingCaseStatus;
use Civi\Funding\FundingCaseTypes\AdB\SammelantragKurs\Traits\KursSupportedFundingCaseTypesTrait;

final class KursApplicationActionsDeterminer extends ApplicationProcessActionsDeterminer {

  use KursSupportedFundingCaseTypesTrait;

  private const STATUS_PERMISSION_ACTIONS_MAP = [
    NULL => [
      'application_create' => ['save', 'save&new', 'save&copy'],
    ],
    'new' => [
      'application_modify' => ['save'],
      'application_apply' => ['apply'],
      'application_withdraw' => ['withdraw', 'delete'],
      'review_content' => ['review', 'add-comment'],
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
      'application_withdraw' => ['withdraw'],
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
    'complete' => [
      'application_withdraw' => ['withdraw'],
      'review_calculative' => ['update', 'add-comment'],
      'review_content' => ['update', 'add-comment'],
    ],
    'rejected' => [
      'review_calculative' => ['reopen'],
      'review_content' => ['reopen'],
    ],
    'withdrawn' => [
      'review_calculative' => ['reopen'],
      'review_content' => ['reopen'],
    ],
  ];

  public function __construct() {
    parent::__construct(
      self::STATUS_PERMISSION_ACTIONS_MAP,
      [FundingCaseStatus::CLEARED],
      reviewStatuses: ['review', 'rework-review'],
      actionNames: ['approve' => ['review' => 'approve', 'rework-review' => 'approve-change']]
    );
  }

  public function getInitialActions(
    array $permissions,
    FundingCaseTypeEntity $fundingCaseType,
    ?FundingCaseEntity $fundingCase
  ): array {
    if (FundingCaseStatus::OPEN === $fundingCase?->getStatus()) {
      // Allow adding applications only after approval.
      return [];
    }

    return parent::getInitialActions($permissions, $fundingCaseType, $fundingCase);
  }

}
