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

final class ReworkPossibleApplicationProcessStatusDeterminer implements ApplicationProcessStatusDeterminerInterface {

  private const STATUS_ACTION_STATUS_MAP = [
    'approved' => [
      'request-rework' => 'rework-requested',
      'update' => 'approved',
    ],
    'rework-requested' => [
      'withdraw-rework-request' => 'approved',
      'approve-rework-request' => 'rework',
      'reject-rework-request' => 'approved',
      'update' => 'rework-requested',
    ],
    'rework' => [
      'save' => 'rework',
      'apply' => 'rework-review-requested',
      'withdraw-change' => 'applied',
      'revert-change' => 'applied',
      'update' => 'rework',
    ],
    'rework-review-requested' => [
      'request-rework' => 'rework',
      'review' => 'rework-review',
      'update' => 'rework-review-requested',
    ],
    'rework-review' => [
      'set-calculative-review-result' => 'rework-review',
      'set-content-review-result' => 'rework-review',
      'request-change' => 'rework',
      'approve-change' => 'approved',
      'reject-change' => 'approved',
      'update' => 'rework-review',
    ],
  ];

  private ApplicationProcessStatusDeterminerInterface $statusDeterminer;

  public function __construct(ApplicationProcessStatusDeterminerInterface $statusDeterminer) {
    $this->statusDeterminer = $statusDeterminer;
  }

  public function getInitialStatus(string $action): string {
    return $this->statusDeterminer->getInitialStatus($action);
  }

  public function getStatus(FullApplicationProcessStatus $currentStatus, string $action): string {
    return self::STATUS_ACTION_STATUS_MAP[$currentStatus->getStatus()][$action]
      ?? $this->statusDeterminer->getStatus($currentStatus, $action);
  }

}
