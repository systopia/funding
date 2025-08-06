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
use Civi\Funding\Entity\ClearingProcessEntityBundle;
use Civi\Funding\FundingCase\FundingCaseStatus;
use CRM_Funding_ExtensionUtil as E;

class ClearingActionsDeterminer {

  private const EDIT_ACTIONS = ['save', 'apply', 'update'];

  private const FUNDING_CASE_FINAL_STATUS_LIST = [FundingCaseStatus::CLEARED];

  private const STATUS_PERMISSION_ACTIONS_MAP = [
    'not-started' => [
      ClearingProcessPermissions::CLEARING_APPLY => ['apply', 'save'],
      ClearingProcessPermissions::CLEARING_MODIFY => ['save'],
    ],
    'draft' => [
      ClearingProcessPermissions::CLEARING_APPLY => ['apply', 'save'],
      ClearingProcessPermissions::CLEARING_MODIFY => ['save'],
      ClearingProcessPermissions::REVIEW_CALCULATIVE => ['review', 'add-comment'],
      ClearingProcessPermissions::REVIEW_CONTENT => ['review', 'add-comment'],
    ],
    'review-requested' => [
      ClearingProcessPermissions::CLEARING_APPLY => ['modify'],
      ClearingProcessPermissions::CLEARING_MODIFY => ['modify'],
      ClearingProcessPermissions::REVIEW_CALCULATIVE => ['review', 'add-comment'],
      ClearingProcessPermissions::REVIEW_CONTENT => ['review', 'add-comment'],
    ],
    'review' => [
      ClearingProcessPermissions::CLEARING_APPLY => [],
      ClearingProcessPermissions::CLEARING_MODIFY => [],
      // Action "update" is required to submit admitted amounts, though other fields will be read only if
      // REVIEW_AMEND permission is not given.
      ClearingProcessPermissions::REVIEW_CALCULATIVE => ['update', 'reject', 'request-change', 'add-comment'],
      ClearingProcessPermissions::REVIEW_CONTENT => ['reject', 'request-change', 'add-comment'],
      ClearingProcessPermissions::REVIEW_AMEND => ['update', 'add-comment'],
    ],
    'rework' => [
      ClearingProcessPermissions::CLEARING_APPLY => ['apply', 'save'],
      ClearingProcessPermissions::CLEARING_MODIFY => ['save'],
      ClearingProcessPermissions::REVIEW_CALCULATIVE => ['review', 'add-comment'],
      ClearingProcessPermissions::REVIEW_CONTENT => ['review', 'add-comment'],
    ],
    'accepted' => [
      ClearingProcessPermissions::CLEARING_APPLY => [],
      ClearingProcessPermissions::CLEARING_MODIFY => [],
      ClearingProcessPermissions::REVIEW_CALCULATIVE => ['request-change', 'review', 'add-comment'],
      ClearingProcessPermissions::REVIEW_CONTENT => ['request-change', 'review', 'add-comment'],
    ],
    'rejected' => [
      ClearingProcessPermissions::CLEARING_APPLY => [],
      ClearingProcessPermissions::CLEARING_MODIFY => [],
      ClearingProcessPermissions::REVIEW_CALCULATIVE => ['request-change', 'review', 'add-comment'],
      ClearingProcessPermissions::REVIEW_CONTENT => ['request-change', 'review', 'add-comment'],
    ],
  ];

  private ClearingCostItemManager $clearingCostItemManager;

  private ClearingResourcesItemManager $clearingResourcesItemManager;

  /**
   * @phpstan-var array<string, string>
   */
  private array $labels;

  public function __construct(
    ClearingCostItemManager $clearingCostItemManager,
    ClearingResourcesItemManager $clearingResourcesItemManager
  ) {
    $this->clearingCostItemManager = $clearingCostItemManager;
    $this->clearingResourcesItemManager = $clearingResourcesItemManager;

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
      'add-comment' => E::ts('Add Comment'),
      'request-change' => E::ts('Request Change'),
      'accept' => E::ts('Accept'),
      'reject' => E::ts('Reject'),
    ];
  }

  /**
   * @phpstan-return array<string, string>
   *   Mapping of action name to label.
   */
  public function getActions(ClearingProcessEntityBundle $clearingProcessBundle): array {
    if ($clearingProcessBundle->getFundingCase()->isStatusIn(self::FUNDING_CASE_FINAL_STATUS_LIST)) {
      return [];
    }

    $fundingCase = $clearingProcessBundle->getFundingCase();
    $permissions = $fundingCase->getPermissions();
    $status = $clearingProcessBundle->getClearingProcess()->getStatus();
    $actions = [];
    foreach ($permissions as $permission) {
      $actions = array_merge($actions, self::STATUS_PERMISSION_ACTIONS_MAP[$status][$permission] ?? []);
    }

    $actions = array_merge($actions, $this->getReviewActions(
      $clearingProcessBundle->getClearingProcess(),
      $fundingCase->hasPermission(ClearingProcessPermissions::REVIEW_CALCULATIVE),
      $fundingCase->hasPermission(ClearingProcessPermissions::REVIEW_CONTENT)
    ));

    return array_filter($this->labels, fn (string $name) => in_array($name, $actions, TRUE), ARRAY_FILTER_USE_KEY);
  }

  public function isActionAllowed(string $action, ClearingProcessEntityBundle $clearingProcessBundle): bool {
    return isset($this->getActions($clearingProcessBundle)[$action]);
  }

  /**
   * @phpstan-param list<string> $actions
   */
  public function isAnyActionAllowed(array $actions, ClearingProcessEntityBundle $clearingProcessBundle): bool {
    return [] !== array_intersect(
      array_keys($this->getActions($clearingProcessBundle)),
      $actions
    );
  }

  public function isAdmittedValueChangeAllowed(ClearingProcessEntityBundle $clearingProcessBundle): bool {
    return $clearingProcessBundle->getFundingCase()->hasPermission(ClearingProcessPermissions::REVIEW_CALCULATIVE)
      && $this->isActionAllowed('update', $clearingProcessBundle);
  }

  public function isContentChangeAllowed(ClearingProcessEntityBundle $clearingProcessBundle): bool {
    $fundingCase = $clearingProcessBundle->getFundingCase();
    $actions = array_keys($this->getActions($clearingProcessBundle));
    if ([] !== array_intersect($actions, ['save', 'apply'])) {
      return TRUE;
    }

    return in_array('update', $actions, TRUE)
      && $fundingCase->hasPermission(ClearingProcessPermissions::REVIEW_AMEND);
  }

  /**
   * @return bool
   *   TRUE if this action may change the content, FALSE if the action may only
   *   change the status and/or add a comment.
   */
  public function isEditAction(string $action): bool {
    return in_array($action, self::EDIT_ACTIONS, TRUE);
  }

  public function isEditAllowed(ClearingProcessEntityBundle $clearingProcessBundle): bool {
    return $this->isAnyActionAllowed(self::EDIT_ACTIONS, $clearingProcessBundle);
  }

  /**
   * @phpstan-return list<string>
   */
  private function getReviewActions(
    ClearingProcessEntity $clearingProcess,
    bool $hasReviewCalculativePermission,
    bool $hasReviewContentPermission
  ): array {
    $actions = [];
    if ('review' === $clearingProcess->getStatus()
      && ($hasReviewCalculativePermission || $hasReviewContentPermission)
    ) {
      if ($hasReviewCalculativePermission) {
        if (TRUE !== $clearingProcess->getIsReviewCalculative()) {
          if ($this->clearingCostItemManager->areAllItemsReviewed($clearingProcess->getId())
            && $this->clearingResourcesItemManager->areAllItemsReviewed($clearingProcess->getId())
          ) {
            $actions[] = 'accept-calculative';
          }
        }
        if (FALSE !== $clearingProcess->getIsReviewCalculative()) {
          $actions[] = 'reject-calculative';
        }
      }
      if ($hasReviewContentPermission) {
        if (TRUE !== $clearingProcess->getIsReviewContent()) {
          $actions[] = 'accept-content';
        }
        if (FALSE !== $clearingProcess->getIsReviewContent()) {
          $actions[] = 'reject-content';
        }
      }
      if (TRUE === $clearingProcess->getIsReviewCalculative() && TRUE === $clearingProcess->getIsReviewContent()) {
        $actions[] = 'accept';
      }
    }

    return $actions;
  }

}
