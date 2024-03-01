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

use Civi\Funding\Entity\ClearingProcessEntity;
use Civi\Funding\Entity\FullClearingProcessStatus;
use Civi\Funding\Permission\Traits\HasReviewPermissionTrait;
use CRM_Funding_ExtensionUtil as E;

final class ClearingActionsDeterminer {

  use HasReviewPermissionTrait;

  // phpcs:disable Generic.Files.LineLength.TooLong
  private const STATUS_PERMISSION_ACTIONS_MAP = [
    'draft' => [
      ClearingProcessPermissions::CLEARING_APPLY => ['apply', 'save'],
      ClearingProcessPermissions::CLEARING_MODIFY => ['save'],
      ClearingProcessPermissions::REVIEW_CALCULATIVE => ['review'],
      ClearingProcessPermissions::REVIEW_CONTENT => ['review'],
    ],
    'review_requested' => [
      ClearingProcessPermissions::CLEARING_APPLY => ['modify'],
      ClearingProcessPermissions::CLEARING_MODIFY => ['modify'],
      ClearingProcessPermissions::REVIEW_CALCULATIVE => ['review'],
      ClearingProcessPermissions::REVIEW_CONTENT => ['review'],
    ],
    'review' => [
      ClearingProcessPermissions::CLEARING_APPLY => [],
      ClearingProcessPermissions::CLEARING_MODIFY => [],
      ClearingProcessPermissions::REVIEW_CALCULATIVE => ['reject', 'request-change', 'update'],
      ClearingProcessPermissions::REVIEW_CONTENT => ['reject', 'request-change', 'update'],
    ],
    'accepted' => [
      ClearingProcessPermissions::CLEARING_APPLY => [],
      ClearingProcessPermissions::CLEARING_MODIFY => [],
      ClearingProcessPermissions::REVIEW_CALCULATIVE => ['request-change', 'review'],
      ClearingProcessPermissions::REVIEW_CONTENT => ['request-change', 'review'],
    ],
    'rejected' => [
      ClearingProcessPermissions::CLEARING_APPLY => [],
      ClearingProcessPermissions::CLEARING_MODIFY => [],
      ClearingProcessPermissions::REVIEW_CALCULATIVE => ['request-change', 'review'],
      ClearingProcessPermissions::REVIEW_CONTENT => ['request-change', 'review'],
    ],
  ];
  // phpcs:enable

  /**
   * @phpstan-var array<string, string>
   */
  private array $labels;

  public function __construct() {
    // Order determines the order returned by getActions().
    $this->labels = [
      'save' => E::ts('Save'),
      'apply' => E::ts('Request Review'),
      'modify' => E::ts('Modify'),
      'review' => E::ts('Review'),
      'update' => E::ts('Save'),
      'accept-content' => E::ts('Accept Content'),
      'reject-content' => E::ts('Reject Content'),
      'accept-calculative' => E::ts('Accept Calculative'),
      'reject-calculative' => E::ts('Reject Calculative'),
      'request-change' => E::ts('Request Change'),
      'accept' => E::ts('Accept'),
      'reject' => E::ts('Reject'),
    ];
  }

  /**
   * @phpstan-param array<string> $permissions
   *
   * @phpstan-return array<string, string>
   *   Mapping of action name to label.
   */
  public function getActions(FullClearingProcessStatus $fullStatus, array $permissions): array {
    $status = $fullStatus->getStatus();
    $actions = [];
    foreach ($permissions as $permission) {
      $actions = array_merge($actions, self::STATUS_PERMISSION_ACTIONS_MAP[$status][$permission] ?? []);
    }

    $actions = array_merge($actions, $this->getReviewActions(
      $fullStatus,
      $this->hasReviewCalculativePermission($permissions),
      $this->hasReviewCalculativePermission($permissions)
    ));

    return array_filter($this->labels, fn (string $name) => in_array($name, $actions, TRUE), ARRAY_FILTER_USE_KEY);
  }

  /**
   * @phpstan-param array<string> $permissions
   */
  public function isActionAllowed(string $action, FullClearingProcessStatus $fullStatus, array $permissions): bool {
    return isset($this->getActions($fullStatus, $permissions)[$action]);
  }

  /**
   * @phpstan-return list<string>
   */
  private function getReviewActions(
    FullClearingProcessStatus $fullStatus,
    bool $hasReviewCalculativePermission,
    bool $hasReviewContentPermission
  ): array {
    $actions = [];
    if ('review' === $fullStatus->getStatus()
      && ($hasReviewCalculativePermission || $hasReviewContentPermission)
    ) {
      if ($hasReviewCalculativePermission) {
        if (TRUE !== $fullStatus->getIsReviewCalculative()) {
          $actions[] = 'accept-calculative';
        }
        if (FALSE !== $fullStatus->getIsReviewCalculative()) {
          $actions[] = 'reject-calculative';
        }
      }
      if ($hasReviewContentPermission) {
        if (TRUE !== $fullStatus->getIsReviewContent()) {
          $actions[] = 'accept-content';
        }
        if (FALSE !== $fullStatus->getIsReviewContent()) {
          $actions[] = 'reject-content';
        }
      }
      if (TRUE === $fullStatus->getIsReviewCalculative() && TRUE === $fullStatus->getIsReviewContent()) {
        $actions[] = 'accept';
      }
    }

    return $actions;
  }

}
