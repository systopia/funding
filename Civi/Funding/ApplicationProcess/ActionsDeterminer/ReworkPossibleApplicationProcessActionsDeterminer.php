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

namespace Civi\Funding\ApplicationProcess\ActionsDeterminer;

final class ReworkPossibleApplicationProcessActionsDeterminer extends ApplicationProcessActionsDeterminer {

  private const STATUS_PERMISSIONS_ACTION_MAP = [
    'approved' => [
      'application_request_rework' => ['request-rework'],
    ],
    'rework-requested' => [
      'application_request_rework' => ['withdraw-rework-request'],
      'review_calculative' => ['approve-rework-request', 'reject-rework-request'],
      'review_content' => ['approve-rework-request', 'reject-rework-request'],
    ],
    'rework' => [
      'application_apply' => ['apply'],
    ],
    'rework-review-requested' => [
      'application_request_rework' => ['request-rework'],
      'review_calculative' => ['review'],
      'review_content' => ['review'],
    ],
    'rework-review' => [
      'review_calculative' => ['approve-calculative', 'reject-calculative'],
      'review_content' => ['approve-content', 'reject-content'],
    ],
  ];

  private ApplicationProcessActionsDeterminerInterface $actionsDeterminer;

  public function __construct(ApplicationProcessActionsDeterminerInterface $actionsDeterminer) {
    $this->actionsDeterminer = $actionsDeterminer;
    parent::__construct(self::STATUS_PERMISSIONS_ACTION_MAP);
  }

  public function getActions(string $status, array $permissions): array {
    return \array_unique(\array_merge(
      parent::getActions($status, $permissions),
      $this->actionsDeterminer->getActions($status, $permissions),
    ));
  }

  public function getInitialActions(array $permissions): array {
    return \array_unique(\array_merge(
      parent::getInitialActions($permissions),
      $this->actionsDeterminer->getInitialActions($permissions),
    ));
  }

}
