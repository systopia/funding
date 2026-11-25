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

use Civi\Funding\ApplicationProcess\StatusDeterminer\AbstractApplicationProcessStatusDeterminerDecorator;
use Civi\Funding\ApplicationProcess\StatusDeterminer\ApplicationProcessStatusDeterminer;

abstract class AbstractDvvApplicationStatusDeterminer extends AbstractApplicationProcessStatusDeterminerDecorator {

  private const STATUS_ACTION_STATUS_MAP = [
    NULL => [
      'save' => 'new',
      'apply' => 'review',
    ],
    'new' => [
      'save' => 'new',
      'apply' => 'review',
      'reject' => 'rejected',
    ],
    'review' => [
      'approve-calculative' => 'review',
      'reject-calculative' => 'review',
      'approve-content' => 'review',
      'reject-content' => 'review',
      'request-change' => 'draft',
      'accept' => 'accepted',
      'reject' => 'rejected',
      'update' => 'review',
      'add-comment' => 'review',
    ],
    'draft' => [
      'save' => 'draft',
      'apply' => 'review',
      'withdraw' => 'withdrawn',
      'add-comment' => 'draft',
      'reject' => 'rejected',
    ],
    'accepted' => [
      'withdraw' => 'withdrawn',
      'update' => 'accepted',
      'add-comment' => 'accepted',
      'request-rework' => 'rework-requested',
      'approve' => 'approved',
    ],
    'approved' => [
      'add-comment' => 'approved',
      'recall' => 'accepted',
    ],
    'complete' => [
      'withdraw' => 'withdrawn',
      'update' => 'complete',
      'add-comment' => 'complete',
    ],
    'rework-requested' => [
      'withdraw-rework-request' => 'accepted',
      'approve-rework-request' => 'rework',
      'reject-rework-request' => 'accepted',
      'add-comment' => 'rework-requested',
    ],
    'rework' => [
      'save' => 'rework',
      'apply' => 'rework-review',
      'withdraw-change' => 'accepted',
      'review' => 'rework-review',
      'add-comment' => 'rework',
    ],
    'rework-review' => [
      'approve-calculative' => 'rework-review',
      'reject-calculative' => 'rework-review',
      'approve-content' => 'rework-review',
      'reject-content' => 'rework-review',
      'request-change' => 'rework',
      'accept-change' => 'accepted',
      'reject-change' => 'accepted',
      'update' => 'rework-review',
      'add-comment' => 'rework-review',
    ],
  ];

  public function __construct() {
    parent::__construct(
      new ApplicationProcessStatusDeterminer(self::STATUS_ACTION_STATUS_MAP)
    );
  }

}
