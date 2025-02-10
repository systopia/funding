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

/**
 * @codeCoverageIgnore
 */
final class DefaultApplicationProcessActionStatusInfo extends AbstractApplicationProcessActionStatusInfo {

  /**
   * @inheritDoc
   */
  public static function getSupportedFundingCaseTypes(): array {
    return [];
  }

  public function getEligibleStatusList(): array {
    return ['eligible', 'complete'];
  }

  public function getFinalIneligibleStatusList(): array {
    return [
      'rejected',
      'withdrawn',
    ];
  }

  public function isApplyAction(string $action): bool {
    return 'apply' === $action;
  }

  public function isDeleteAction(string $action): bool {
    return 'delete' === $action;
  }

  public function isInWorkStatus(string $status): bool {
    return in_array($status, ['new', 'draft'], TRUE);
  }

  public function isRestoreAction(string $action): bool {
    return FALSE;
  }

  public function isReviewStartAction(string $action): bool {
    return 'review' === $action;
  }

  public function isReviewStatus(string $status): bool {
    return 'review' === $status;
  }

  public function isSnapshotRequiredStatus(FullApplicationProcessStatus $status): bool {
    return in_array($status->getStatus(), ['eligible', 'complete'], TRUE);
  }

  public function isRejectedStatus(string $status): bool {
    return 'rejected' === $status;
  }

  public function isWithdrawnStatus(string $status): bool {
    return 'withdrawn' === $status;
  }

}
