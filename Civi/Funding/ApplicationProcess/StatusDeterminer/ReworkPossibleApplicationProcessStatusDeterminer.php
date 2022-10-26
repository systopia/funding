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

final class ReworkPossibleApplicationProcessStatusDeterminer implements ApplicationProcessStatusDeterminerInterface {

  private const STATUS_ACTION_STATUS_MAP = [
    'approved' => [
      'request-rework' => 'rework-requested',
    ],
    'rework-requested' => [
      'withdraw-rework-request' => 'approved',
      'approve-rework-request' => 'rework',
      'reject-rework-request' => 'approved',
    ],
    'rework' => [
      'apply' => 'rework-review-requested',
    ],
    'rework-review-requested' => [
      'request-rework' => 'rework',
      'review' => 'rework-review',
    ],
    'rework-review' => [
      // @todo When to switch to "approved"
      'approve-calculative' => 'rework-review',
      'approve-content' => 'rework-review',
      'reject-calculative' => 'rework',
      'reject-content' => 'rework',
    ],
  ];

  private ApplicationProcessStatusDeterminerInterface $statusDeterminer;

  public function __construct(ApplicationProcessStatusDeterminerInterface $statusDeterminer) {
    $this->statusDeterminer = $statusDeterminer;
  }

  public function getInitialStatus(string $action): string {
    return $this->statusDeterminer->getInitialStatus($action);
  }

  public function getStatus(string $currentStatus, string $action): string {
    return self::STATUS_ACTION_STATUS_MAP[$currentStatus][$action]
      ?? $this->statusDeterminer->getStatus($currentStatus, $action);
  }

}
