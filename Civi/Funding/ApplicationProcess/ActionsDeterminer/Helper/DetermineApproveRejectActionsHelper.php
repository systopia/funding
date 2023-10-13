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

  /**
   * @phpstan-var array<string, string> Mapping of status to action name.
   */
  private array $approveActions;

  /**
   * @phpstan-var array<string, string> Mapping of status to action name.
   */
  private array $approveCalculativeActions;

  /**
   * @phpstan-var array<string, string> Mapping of status to action name.
   */
  private array $approveContentActions;

  /**
   * @phpstan-var array<string, string> Mapping of status to action name.
   */
  private array $rejectCalculativeActions;

  /**
   * @phpstan-var array<string, string> Mapping of status to action name.
   */
  private array $rejectContentActions;

  /**
   * @phpstan-var array<string>
   */
  private array $reviewStatusList;

  /**
   * @phpstan-param array<string> $reviewStatusList
   *   If an application is in one of these status it is in review.
   * @phpstan-param array{
   *   approve?: string|array<string, string>,
   *   approve-calculative?: string|array<string, string>,
   *   approve-content?: string|array<string, string>,
   *   reject-calculative?: string|array<string, string>,
   *   reject-content?: string|array<string, string>,
   * } $actionNames
   *   Allows to specify custom action names either as simple string to use for
   *   every review status, or as mapping of status to action name.
   */
  public function __construct(array $reviewStatusList = ['review'], array $actionNames = []) {
    $this->reviewStatusList = $reviewStatusList;
    $this->approveActions = $this->buildActionList($actionNames['approve'] ?? NULL, 'approve');
    $this->approveCalculativeActions = $this->buildActionList(
      $actionNames['approve-calculative'] ?? NULL,
      'approve-calculative'
    );
    $this->approveContentActions = $this->buildActionList($actionNames['approve-content'] ?? NULL, 'approve-content');
    $this->rejectCalculativeActions = $this->buildActionList(
      $actionNames['reject-calculative'] ?? NULL,
      'reject-calculative'
    );
    $this->rejectContentActions = $this->buildActionList($actionNames['reject-content'] ?? NULL, 'reject-content');
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
          $actions[] = $this->approveCalculativeActions[$status->getStatus()];
        }
        if (FALSE !== $status->getIsReviewCalculative()) {
          $actions[] = $this->rejectCalculativeActions[$status->getStatus()];
        }
      }
      if ($hasReviewContentPermission) {
        if (TRUE !== $status->getIsReviewContent()) {
          $actions[] = $this->approveContentActions[$status->getStatus()];
        }
        if (FALSE !== $status->getIsReviewContent()) {
          $actions[] = $this->rejectContentActions[$status->getStatus()];
        }
      }
      if (TRUE === $status->getIsReviewCalculative() && TRUE === $status->getIsReviewContent()) {
        $actions[] = $this->approveActions[$status->getStatus()];
      }
    }

    return $actions;
  }

  /**
   * @phpstan-param array<string, string>|string|null $param
   *
   * @phpstan-return array<string, string> Mapping of status to action name.
   */
  private function buildActionList($param, string $default): array {
    $actionList = [];
    if (is_string($param)) {
      $default = $param;
    }
    elseif (is_array($param)) {
      $actionList = $param;
    }

    foreach ($this->reviewStatusList as $status) {
      $actionList[$status] ??= $default;
    }

    return $actionList;
  }

}
