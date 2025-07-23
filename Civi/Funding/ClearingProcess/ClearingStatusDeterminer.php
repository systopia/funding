<?php
/*
 * Copyright (C) 2024 SYSTOPIA GmbH
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

namespace Civi\Funding\ClearingProcess;

use Civi\Funding\Entity\FullClearingProcessStatus;

class ClearingStatusDeterminer {

  private const STATUS_ACTION_STATUS_MAP = [
    'not-started' => [
      'save' => 'draft',
      'apply' => 'review-requested',
    ],
    'draft' => [
      'save' => 'draft',
      'apply' => 'review-requested',
      'review' => 'review',
    ],
    'review-requested' => [
      'modify' => 'draft',
      'review' => 'review',
    ],
    'review' => [
      'update' => 'review',
      'request-change' => 'rework',
      'accept-calculative' => 'review',
      'reject-calculative' => 'review',
      'accept-content' => 'review',
      'reject-content' => 'review',
      'reject' => 'rejected',
      'accept' => 'accepted',
    ],
    'rework' => [
      'save' => 'rework',
      'apply' => 'review-requested',
      'review' => 'review',
    ],
    'accepted' => [
      'review' => 'review',
      'request-change' => 'rework',
    ],
    'rejected' => [
      'review' => 'review',
      'request-change' => 'rework',
    ],
  ];

  public function getStatus(FullClearingProcessStatus $currentStatus, string $action): FullClearingProcessStatus {
    $status = self::STATUS_ACTION_STATUS_MAP[$currentStatus->getStatus()][$action] ?? NULL;

    if (NULL === $status) {
      throw new \InvalidArgumentException(
        sprintf('Invalid combination of action ("%s") and status ("%s")', $action, $currentStatus->getStatus())
      );
    }

    return new FullClearingProcessStatus(
      $status,
      $this->getIsReviewCalculative($currentStatus, $action),
      $this->getIsReviewContent($currentStatus, $action)
    );
  }

  private function getIsReviewCalculative(FullClearingProcessStatus $currentStatus, string $action): ?bool {
    if ('request-change' === $action) {
      return NULL;
    }

    if ('accept-calculative' === $action) {
      return TRUE;
    }

    if ('reject-calculative' === $action) {
      return FALSE;
    }

    return $currentStatus->getIsReviewCalculative();
  }

  private function getIsReviewContent(FullClearingProcessStatus $currentStatus, string $action): ?bool {
    if ('request-change' === $action) {
      return NULL;
    }

    if ('accept-content' === $action) {
      return TRUE;
    }

    if ('reject-content' === $action) {
      return FALSE;
    }

    return $currentStatus->getIsReviewContent();
  }

}
