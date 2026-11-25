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

namespace Civi\Funding\FundingCaseTypes\DVV;

use Civi\Funding\FundingCaseType\MetaData\AbstractFundingCaseTypeMetaData;
use Civi\Funding\FundingCaseType\MetaData\ApplicationProcessAction;
use Civi\Funding\FundingCaseType\MetaData\ApplicationProcessStatus;
use Civi\Funding\FundingCaseType\MetaData\DefaultApplicationProcessActions;
use Civi\Funding\FundingCaseType\MetaData\DefaultApplicationProcessStatuses;
use Civi\Funding\FundingCaseType\MetaData\ReworkApplicationProcessActions;
use Civi\Funding\FundingCaseType\MetaData\ReworkApplicationProcessStatuses;

abstract class AbstractDvvFundingCaseTypeMetaData extends AbstractFundingCaseTypeMetaData {

  /**
   * @var non-empty-array<string, \Civi\Funding\FundingCaseType\MetaData\ApplicationProcessAction>|null
   */
  private ?array $applicationProcessActions = NULL;

  /**
   * @var non-empty-array<string, \Civi\Funding\FundingCaseType\MetaData\ApplicationProcessStatus>|null
   */
  private ?array $applicationProcessStatuses = NULL;

  /**
   * @inheritDoc
   */
  public function getApplicationProcessActions(): array {
    return $this->applicationProcessActions ??= [
      // Applicant actions.
      'save' => DefaultApplicationProcessActions::save(),
      'apply' => DefaultApplicationProcessActions::apply(
        'Einreichen',
        'Nach der Einreichung kann der Antrag nicht mehr verändert werden. Wollen Sie fortfahren?'
      ),
      'withdraw' => DefaultApplicationProcessActions::withdraw(),
      'delete' => DefaultApplicationProcessActions::delete(),
      // Reviewer actions.
      'review' => DefaultApplicationProcessActions::review(),
      'approve-calculative' => DefaultApplicationProcessActions::approveCalculative(),
      'reject-calculative' => DefaultApplicationProcessActions::rejectCalculative(),
      'approve-content' => DefaultApplicationProcessActions::approveContent(),
      'reject-content' => DefaultApplicationProcessActions::rejectContent(),
      'request-change' => DefaultApplicationProcessActions::requestChange(),
      'accept' => new ApplicationProcessAction([
        'name' => 'accept',
        'label' => 'Annehmen',
        'batchPossible' => TRUE,
      ]),
      'approve' => DefaultApplicationProcessActions::approve(),
      'retract' => new ApplicationProcessAction([
        'name' => 'retract',
        'label' => 'Bewilligung zurückziehen',
        'batchPossible' => TRUE,
      ]),
      'reject' => DefaultApplicationProcessActions::reject(),
      'move-to-new-funding-case' => DefaultApplicationProcessActions::moveToNewFundingCase(),
      // Rework: Applicant actions.
      'request-rework' => ReworkApplicationProcessActions::requestRework(),
      'withdraw-rework-request' => ReworkApplicationProcessActions::withdrawReworkRequest(),
      'withdraw-change' => ReworkApplicationProcessActions::withdrawChange(),
      'withdraw-rework' => ReworkApplicationProcessActions::withdrawRework(),
      // Rework: Reviewer actions.
      'approve-rework-request' => ReworkApplicationProcessActions::approveReworkRequest(),
      'reject-rework-request' => ReworkApplicationProcessActions::rejectReworkRequest(),
      'accept-change' => ReworkApplicationProcessActions::approveChange('accept-change'),
      'reject-change' => ReworkApplicationProcessActions::rejectChange(),
    ];
  }

  /**
   * @inheritDoc
   */
  public function getApplicationProcessStatuses(): array {
    return $this->applicationProcessStatuses ??= [
      'complete' => DefaultApplicationProcessStatuses::complete(),
      'draft' => DefaultApplicationProcessStatuses::draft(),
      'accepted' => new ApplicationProcessStatus([
        'name' => 'accepted',
        'label' => 'Förderfähig',
        'icon' => 'fa-check-circle-o',
        'snapshotRequired' => ApplicationProcessStatus::SNAPSHOT_ON_ENTER_OR_DATA_CHANGED,
      ]),
      'approved' => DefaultApplicationProcessStatuses::eligible('approved', 'Bewilligt'),
      'new' => DefaultApplicationProcessStatuses::new(),
      'rejected' => DefaultApplicationProcessStatuses::rejected(),
      'review' => DefaultApplicationProcessStatuses::review(),
      'withdrawn' => DefaultApplicationProcessStatuses::withdrawn(),
      'rework' => ReworkApplicationProcessStatuses::rework(),
      'rework-requested' => ReworkApplicationProcessStatuses::reworkRequested(),
      'rework-review' => ReworkApplicationProcessStatuses::reworkReview(),
    ];
  }

}
