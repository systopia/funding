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

use Civi\Funding\ApplicationProcess\StatusDeterminer\AbstractApplicationProcessStatusDeterminer;
use Civi\Funding\Entity\FullApplicationProcessStatus;
use Civi\Funding\FundingCaseTypes\BSH\HiHAktion\Traits\HiHSupportedFundingCaseTypesTrait;

final class HiHApplicationStatusDeterminer extends AbstractApplicationProcessStatusDeterminer {

  use HiHSupportedFundingCaseTypesTrait;

  private const STATUS_ACTION_STATUS_MAP = [
    NULL => [
      'save' => 'new',
      'apply' => 'applied',
    ],
    'new' => [
      'save' => 'new',
      'apply' => 'applied',
      'withdraw' => 'withdrawn',
    ],
    'applied' => [
      'modify' => 'draft',
      'withdraw' => 'withdrawn',
      'review' => 'review',
      'add-comment' => 'applied',
    ],
    'review' => [
      'request-change' => 'draft',
      'release' => 'advisory',
      'reject' => 'rejected',
      'update' => 'review',
      'add-comment' => 'review',
    ],
    'draft' => [
      'save' => 'draft',
      'apply' => 'applied',
      'withdraw' => 'withdrawn',
      'add-comment' => 'draft',
    ],
    'advisory' => [
      'reject' => 'rejected_after_advisory',
      'add-comment' => 'advisory',
      're-apply' => 'applied',
      // Maybe changed to "approved_partial" by a subscriber.
      'approve' => 'approved',
      'update' => 'advisory',
    ],
    'approved' => [
      'add-comment' => 'approved',
      'update' => 'approved',
      'approve-update' => 'approved',
      'recall' => 'advisory',
    ],
    'approved_partial' => [
      'add-comment' => 'approved_partial',
      'update' => 'approved_partial',
      'approve-update' => 'approved_partial',
      'recall' => 'advisory',
    ],
    'rejected' => [
      're-apply' => 'applied',
      'add-comment' => 'rejected',
    ],
    'rejected_after_advisory' => [
      're-release' => 'advisory',
      'add-comment' => 'rejected_after_advisory',
    ],
    'complete' => [
      'add-comment' => 'complete',
      'approve-update' => 'complete',
    ],
  ];

  public function __construct() {
    parent::__construct(self::STATUS_ACTION_STATUS_MAP);
  }

  public function getStatusOnClearingProcessStarted(FullApplicationProcessStatus $currentStatus
  ): FullApplicationProcessStatus {
    return new FullApplicationProcessStatus(
      'complete',
      $currentStatus->getIsReviewCalculative(),
      $currentStatus->getIsReviewContent()
    );
  }

  protected function getIsReviewCalculative(FullApplicationProcessStatus $currentStatus, string $action): ?bool {
    if ('request-change' === $action) {
      return NULL;
    }

    if ('release' === $action) {
      return TRUE;
    }

    if ('reject' === $action) {
      return $currentStatus->getIsReviewCalculative() ?? FALSE;
    }

    return $currentStatus->getIsReviewCalculative();
  }

  protected function getIsReviewContent(FullApplicationProcessStatus $currentStatus, string $action): ?bool {
    if ('request-change' === $action) {
      return NULL;
    }

    if ('release' === $action) {
      return TRUE;
    }

    if ('reject' === $action) {
      return $currentStatus->getIsReviewContent() ?? FALSE;
    }

    return $currentStatus->getIsReviewContent();
  }

}
