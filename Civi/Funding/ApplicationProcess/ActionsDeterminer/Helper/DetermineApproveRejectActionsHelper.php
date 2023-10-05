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

namespace Civi\Funding\ApplicationProcess\ActionsDeterminer\Helper;

use Civi\Funding\Entity\FullApplicationProcessStatus;

/**
 * Helper class to determine approve/reject actions.
 */
final class DetermineApproveRejectActionsHelper {

  private string $approveAction;

  private string $approveCalculativeAction;

  private string $approveContentAction;

  private string $rejectCalculativeAction;

  private string $rejectContentAction;

  /**
   * @phpstan-var array<string>
   */
  private array $reviewStatusList;

  /**
   * @phpstan-param array<string> $reviewStatusList
   *   If an application is in one of these status it is in review.
   * @phpstan-param array{
   *   approve?: string,
   *   approve-calculative?: string,
   *   approve-content?: string,
   *   reject-calculative?: string,
   *   reject-content?: string,
   * } $actionNames Allows to specify custom action names.
   */
  public function __construct(array $reviewStatusList = ['review'], array $actionNames = []) {
    $this->approveAction = $actionNames['approve'] ?? 'approve';
    $this->approveCalculativeAction = $actionNames['approve-calculative'] ?? 'approve-calculative';
    $this->approveContentAction = $actionNames['approve-content'] ?? 'approve-content';
    $this->rejectCalculativeAction = $actionNames['reject-calculative'] ?? 'reject-calculative';
    $this->rejectContentAction = $actionNames['reject-content'] ?? 'reject-content';
    $this->reviewStatusList = $reviewStatusList;
  }

  /**
   * @phpstan-return array<string>
   */
  public function getActions(
    FullApplicationProcessStatus $status,
    bool $hasReviewCalculativePermission,
    bool $hasReviewContentPermission
  ): array {
    $actions = [];
    if (in_array($status->getStatus(), $this->reviewStatusList, TRUE)
      && ($hasReviewCalculativePermission || $hasReviewContentPermission)
    ) {
      if ($hasReviewCalculativePermission) {
        if (TRUE !== $status->getIsReviewCalculative()) {
          $actions[] = $this->approveCalculativeAction;
        }
        if (FALSE !== $status->getIsReviewCalculative()) {
          $actions[] = $this->rejectCalculativeAction;
        }
      }
      if ($hasReviewContentPermission) {
        if (TRUE !== $status->getIsReviewContent()) {
          $actions[] = $this->approveContentAction;
        }
        if (FALSE !== $status->getIsReviewContent()) {
          $actions[] = $this->rejectContentAction;
        }
      }
      if (TRUE === $status->getIsReviewCalculative() && TRUE === $status->getIsReviewContent()) {
        $actions[] = $this->approveAction;
      }
    }

    return $actions;
  }

}
