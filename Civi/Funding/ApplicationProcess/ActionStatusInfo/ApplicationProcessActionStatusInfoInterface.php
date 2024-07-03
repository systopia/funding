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

namespace Civi\Funding\ApplicationProcess\ActionStatusInfo;

use Civi\Funding\Entity\FullApplicationProcessStatus;

interface ApplicationProcessActionStatusInfoInterface {

  public const SERVICE_TAG = 'funding.application.action_status_info';

  /**
   * @phpstan-return list<string>
   */
  public static function getSupportedFundingCaseTypes(): array;

  /**
   * @phpstan-return array<string>
   *   Status of applications that are eligible.
   */
  public function getEligibleStatusList(): array;

  /**
   * @phpstan-return array<string>
   *   All status that are final states excluding those dedicated for eligible
   *   applications, i.e. applications in such a status cannot become eligible
   *   anymore. Usually withdrawn and rejected.
   */
  public function getFinalIneligibleStatusList(): array;

  public function isApplyAction(string $action): bool;

  public function isChangeRequiredStatus(string $status): bool;

  public function isDeleteAction(string $action): bool;

  /**
   * @return bool|null
   *   TRUE if eligible, FALSE if ineligible, NULL if not decided, yet.
   */
  public function isEligibleStatus(string $status): ?bool;

  public function isRestoreAction(string $action): bool;

  public function isReviewStartAction(string $action): bool;

  public function isReviewStatus(string $status): bool;

  public function isSnapshotRequiredStatus(FullApplicationProcessStatus $status): bool;

  public function isWithdrawnStatus(string $status): bool;

}
