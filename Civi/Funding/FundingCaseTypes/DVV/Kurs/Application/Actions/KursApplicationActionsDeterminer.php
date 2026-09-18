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

namespace Civi\Funding\FundingCaseTypes\DVV\Kurs\Application\Actions;

use Civi\Funding\ApplicationProcess\ActionsDeterminer\AbstractApplicationActionsDeterminerDecorator;
use Civi\Funding\ApplicationProcess\ActionsDeterminer\ApplicationProcessActionsDeterminer;
use Civi\Funding\ClearingProcess\ClearingProcessPermissions;
use Civi\Funding\FundingCaseTypes\DVV\Kurs\Traits\KursSupportedFundingCaseTypesTrait;

final class KursApplicationActionsDeterminer extends AbstractApplicationActionsDeterminerDecorator {

  use KursSupportedFundingCaseTypesTrait;

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
      'review_calculative' => ['add-comment'],
      'review_content' => ['add-comment'],
      ClearingProcessPermissions::CLEARING_APPLY => ['add-applicant-comment'],
      ClearingProcessPermissions::CLEARING_MODIFY => ['add-applicant-comment'],
    ],
    'open&eligible' => [
      'review_calculative' => ['move-to-new-funding-case'],
      'review_content' => ['move-to-new-funding-case'],
    ],
    'ongoing&eligible' => [
      'application_request_rework' => ['request-rework', 'add-applicant-comment'],
      'review_calculative' => ['update'],
      'review_content' => ['update'],
    ],
    'complete' => [
      'application_apply' => ['add-applicant-comment'],
      'application_modify' => ['add-applicant-comment'],
      'application_withdraw' => ['withdraw'],
      'review_calculative' => ['add-comment'],
      'review_content' => ['add-comment'],
      ClearingProcessPermissions::CLEARING_APPLY => ['add-applicant-comment'],
      ClearingProcessPermissions::CLEARING_MODIFY => ['add-applicant-comment'],
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
      'review_calculative' => ['add-comment'],
      'review_content' => ['add-comment'],
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

  public function __construct() {
    parent::__construct(
      new ApplicationProcessActionsDeterminer(
        self::STATUS_PERMISSION_ACTIONS_MAP,
        reviewStatuses: ['review', 'rework-review'],
        actionNames: [
          'approve' => [
            'review' => 'approve',
            'rework-review' => 'approve-change',
          ],
        ]
      )
    );
  }

}
