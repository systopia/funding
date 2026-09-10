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
use Civi\Funding\ClearingProcess\ClearingProcessPermissions;
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
    'eligible' => [
      'application_apply' => ['add-applicant-comment'],
      'application_modify' => ['modify', 'add-applicant-comment'],
      'application_withdraw' => ['withdraw'],
      'review_calculative' => ['update', 'add-comment'],
      'review_content' => ['update', 'add-comment'],
    ],
    'rework' => [
      'application_apply' => ['save', 'add-applicant-comment'],
      'application_modify' => ['save', 'add-applicant-comment'],
      'application_withdraw' => ['withdraw-change'],
      'review_calculative' => ['add-comment'],
      'review_content' => ['add-comment'],
      ClearingProcessPermissions::CLEARING_APPLY => ['add-applicant-comment'],
      ClearingProcessPermissions::CLEARING_MODIFY => ['add-applicant-comment'],
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
    'rejected' => [
      'review_calculative' => ['reopen', 'add-comment'],
      'review_content' => ['reopen', 'add-comment'],
    ],
    'withdrawn' => [
      'review_calculative' => ['reopen', 'add-comment'],
      'review_content' => ['reopen', 'add-comment'],
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
