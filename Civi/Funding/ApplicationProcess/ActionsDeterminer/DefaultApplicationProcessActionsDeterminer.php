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

final class DefaultApplicationProcessActionsDeterminer extends ApplicationProcessActionsDeterminer {

  private const STATUS_PERMISSION_ACTIONS_MAP = [
    NULL => [
      'application_create' => ['save'],
      'application_apply' => ['apply'],
    ],
    'new' => [
      'application_modify' => ['save'],
      'application_apply' => ['apply'],
      'application_withdraw' => ['delete'],
    ],
    'applied' => [
      'application_modify' => ['modify'],
      'application_withdraw' => ['withdraw'],
      'review_calculative' => ['review', 'add-comment'],
      'review_content' => ['review', 'add-comment'],
    ],
    'review' => [
      'review_calculative' => ['request-change', 'update', 'reject', 'add-comment'],
      'review_content' => ['request-change', 'update', 'reject', 'add-comment'],
    ],
    'draft' => [
      'application_modify' => ['save'],
      'application_apply' => ['apply'],
      'application_withdraw' => ['withdraw'],
      'review_calculative' => ['review', 'add-comment'],
      'review_content' => ['review', 'add-comment'],
    ],
    'eligible' => [
      'review_calculative' => ['update', 'add-comment'],
      'review_content' => ['update', 'add-comment'],
    ],
  ];

  public function __construct() {
    parent::__construct(self::STATUS_PERMISSION_ACTIONS_MAP);
  }

  public function getActions(FullApplicationProcessStatus $status, array $permissions): array {
    $actions = parent::getActions($status, $permissions);
    if ('review' === $status->getStatus() && $this->hasReviewPermission($permissions)) {
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
        $actions[] = 'approve';
      }
    }

    return $actions;
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
