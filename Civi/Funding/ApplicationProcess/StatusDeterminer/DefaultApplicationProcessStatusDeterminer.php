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

namespace Civi\Funding\ApplicationProcess\StatusDeterminer;

use Civi\Funding\Entity\FullApplicationProcessStatus;

final class DefaultApplicationProcessStatusDeterminer extends AbstractApplicationProcessStatusDeterminer {

  private const STATUS_ACTION_STATUS_MAP = [
    NULL => [
      'save' => 'new',
      'apply' => 'applied',
    ],
    'new' => [
      'save' => 'new',
      'apply' => 'applied',
    ],
    'applied' => [
      'modify' => 'draft',
      'withdraw' => 'withdrawn',
      'review' => 'review',
      'add-comment' => 'applied',
    ],
    'review' => [
      'approve-calculative' => 'review',
      'reject-calculative' => 'review',
      'approve-content' => 'review',
      'reject-content' => 'review',
      'request-change' => 'draft',
      'approve' => 'eligible',
      'reject' => 'rejected',
      'update' => 'review',
      'add-comment' => 'review',
    ],
    'draft' => [
      'save' => 'draft',
      'apply' => 'applied',
      'withdraw' => 'withdrawn',
      'review' => 'review',
      'add-comment' => 'draft',
    ],
    'eligible' => [
      'update' => 'eligible',
      'add-comment' => 'eligible',
    ],
  ];

  public function __construct() {
    parent::__construct(self::STATUS_ACTION_STATUS_MAP);
  }

  protected function getIsReviewCalculative(FullApplicationProcessStatus $currentStatus, string $action): ?bool {
    if ('request-change' === $action) {
      return NULL;
    }

    if ('approve-calculative' === $action) {
      return TRUE;
    }

    if ('reject-calculative' === $action) {
      return FALSE;
    }

    return $currentStatus->getIsReviewCalculative();
  }

  protected function getIsReviewContent(FullApplicationProcessStatus $currentStatus, string $action): ?bool {
    if ('request-change' === $action) {
      return NULL;
    }

    if ('approve-content' === $action) {
      return TRUE;
    }

    if ('reject-content' === $action) {
      return FALSE;
    }

    return $currentStatus->getIsReviewContent();
  }

}
