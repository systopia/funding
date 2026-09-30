<?php
/*
 * Copyright (C) 2026 SYSTOPIA GmbH
 *
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License as published by the Free
 * Software Foundation, either version 3 of the License, or (at your option) any
 * later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types = 1);

namespace Civi\Funding\ApplicationProcess\ActionsDeterminer;

use Civi\Funding\ApplicationProcess\ActionsDeterminer\Helper\DetermineApproveRejectActionsHelper;
use Civi\Funding\Entity\ApplicationProcessEntityBundle;
use Civi\Funding\FundingCase\FundingCaseStatus;

/**
 * @phpstan-import-type statusPermissionsActionMapT from AbstractApplicationProcessActionsDeterminer
 */
class ApplicationProcessActionsDeterminer extends AbstractApplicationProcessActionsDeterminer {

  private DetermineApproveRejectActionsHelper $determineApproveRejectActionsHelper;

  /**
   * @phpsta-param statusPermissionsActionMapT $statusPermissionActionsMap
   *
   * @param list<string> $fundingCaseFinalStatuses
   *  In these statuses the funding case is final, i.e. no actions are allowed
   *  in the application process.
   * @param list<string> $reviewCalculativePermissions
   *   List of permissions that are allowed to perform calculative reviews.
   * @param list<string> $reviewContentPermissions
   *   List of permissions that are allowed to perform content reviews.
   * @param list<string> $reviewStatuses
   *   See {@link DetermineApproveRejectActionsHelper::__construct()}.
   * @phpstan-param array{
   *   approve?: string|array<string, string>,
   *   approve-calculative?: string|array<string, string>,
   *   approve-content?: string|array<string, string>,
   *   reject-calculative?: string|array<string, string>,
   *   reject-content?: string|array<string, string>,
   * } $actionNames
   *   See {@link DetermineApproveRejectActionsHelper::__construct()}.
   */
  public function __construct(
    array $statusPermissionActionsMap,
    private readonly array $fundingCaseFinalStatuses = [FundingCaseStatus::CLEARED],
    private readonly array $reviewCalculativePermissions = ['review_calculative'],
    private readonly array $reviewContentPermissions = ['review_content'],
    array $reviewStatuses = ['review'],
    array $actionNames = []
  ) {
    parent::__construct($statusPermissionActionsMap);
    $this->determineApproveRejectActionsHelper = new DetermineApproveRejectActionsHelper($reviewStatuses, $actionNames);
  }

  public function getActions(ApplicationProcessEntityBundle $applicationProcessBundle, array $statusList): array {
    if (!$this->isStatusMapApplicable($applicationProcessBundle, $statusList)) {
      return [];
    }

    $permissions = $applicationProcessBundle->getFundingCase()->getPermissions();

    return array_merge(
      parent::getActions($applicationProcessBundle, $statusList),
      $this->determineApproveRejectActionsHelper->getActions(
        $applicationProcessBundle->getApplicationProcess()->getFullStatus(),
        $this->hasReviewCalculativePermission($permissions),
        $this->hasReviewContentPermission($permissions)
      ),
    );
  }

  /**
   * @param array<int, \Civi\Funding\Entity\FullApplicationProcessStatus> $statusList
   *   Status of other application processes in same funding case indexed by ID.
   *
   * @return bool
   *   TRUE if status map can be applied, FALSE otherwise.
   */
  protected function isStatusMapApplicable(
    ApplicationProcessEntityBundle $applicationProcessBundle,
    array $statusList
  ): bool {
    return !$applicationProcessBundle->getFundingCase()->isStatusIn($this->fundingCaseFinalStatuses);
  }

  /**
   * @phpstan-param array<string> $permissions
   */
  protected function hasReviewPermission(array $permissions): bool {
    return $this->hasReviewCalculativePermission($permissions) || $this->hasReviewContentPermission($permissions);
  }

  /**
   * @phpstan-param array<string> $permissions
   */
  protected function hasReviewCalculativePermission(array $permissions): bool {
    return [] !== array_intersect($this->reviewCalculativePermissions, $permissions);
  }

  /**
   * @phpstan-param array<string> $permissions
   */
  protected function hasReviewContentPermission(array $permissions): bool {
    return [] !== array_intersect($this->reviewContentPermissions, $permissions);
  }

}
