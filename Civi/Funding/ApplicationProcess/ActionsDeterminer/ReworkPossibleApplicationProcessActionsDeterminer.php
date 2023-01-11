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

use Civi\Funding\Entity\FullApplicationProcessStatus;

final class ReworkPossibleApplicationProcessActionsDeterminer extends ApplicationProcessActionsDeterminer {

  private const STATUS_PERMISSIONS_ACTION_MAP = [
    'approved' => [
      'application_request_rework' => ['request-rework'],
    ],
    'rework-requested' => [
      'application_request_rework' => ['withdraw-rework-request'],
      'review_calculative' => ['approve-rework-request', 'reject-rework-request', 'add-comment'],
      'review_content' => ['approve-rework-request', 'reject-rework-request', 'add-comment'],
    ],
    'rework' => [
      'application_apply' => ['apply'],
      'application_modify' => ['save'],
      'application_withdraw' => ['withdraw-change'],
      'review_calculative' => ['review', 'add-comment'],
      'review_content' => ['review', 'add-comment'],
    ],
    'rework-review-requested' => [
      'application_modify' => ['request-rework'],
      'review_calculative' => ['review', 'add-comment'],
      'review_content' => ['review', 'add-comment'],
    ],
    'rework-review' => [
      'review_calculative' => ['request-change', 'update', 'reject-change', 'add-comment'],
      'review_content' => ['request-change', 'update', 'reject-change', 'add-comment'],
    ],
  ];

  private ApplicationProcessActionsDeterminerInterface $actionsDeterminer;

  public function __construct(ApplicationProcessActionsDeterminerInterface $actionsDeterminer) {
    $this->actionsDeterminer = $actionsDeterminer;
    parent::__construct(self::STATUS_PERMISSIONS_ACTION_MAP);
  }

  public function getActions(FullApplicationProcessStatus $status, array $permissions): array {
    $actions = \array_values(\array_unique(\array_merge(
      parent::getActions($status, $permissions),
      $this->actionsDeterminer->getActions($status, $permissions),
    )));
    if ('rework-review' === $status->getStatus() && $this->hasReviewPermission($permissions)) {
      if ($this->hasReviewCalculativePermission($permissions)) {
        if (TRUE !== $status->getIsReviewCalculative()) {
          $actions[] = 'approve-calculative';
        }
        if (FALSE !== $status->getIsReviewCalculative()) {
          $actions[] = 'reject-calculative';
        }
      }
      if ($this->hasReviewContentPermission($permissions)) {
        if (TRUE !== $status->getIsReviewContent()) {
          $actions[] = 'approve-content';
        }
        if (FALSE !== $status->getIsReviewContent()) {
          $actions[] = 'reject-content';
        }
      }
      if (TRUE === $status->getIsReviewCalculative() && TRUE === $status->getIsReviewContent()) {
        $actions[] = 'approve-change';
      }
    }

    return $actions;
  }

  public function getInitialActions(array $permissions): array {
    return \array_unique(\array_merge(
      parent::getInitialActions($permissions),
      $this->actionsDeterminer->getInitialActions($permissions),
    ));
  }

  /**
   * @phpstan-param array<string> $permissions
   */
  private function hasReviewPermission(array $permissions): bool {
    return in_array('review_content', $permissions, TRUE)
      || in_array('review_calculative', $permissions, TRUE);
  }

  /**
   * @phpstan-param array<string> $permissions
   */
  private function hasReviewCalculativePermission(array $permissions): bool {
    return in_array('review_calculative', $permissions, TRUE);
  }

  /**
   * @phpstan-param array<string> $permissions
   */
  private function hasReviewContentPermission(array $permissions): bool {
    return in_array('review_content', $permissions, TRUE);
  }

}
