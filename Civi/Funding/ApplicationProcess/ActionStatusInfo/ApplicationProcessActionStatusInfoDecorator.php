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
class ApplicationProcessActionStatusInfoDecorator implements ApplicationProcessActionStatusInfoInterface {

  private ApplicationProcessActionStatusInfoInterface $info;

  public function __construct(ApplicationProcessActionStatusInfoInterface $info) {
    $this->info = $info;
  }

  public function getEligibleStatusList(): array {
    return $this->info->getEligibleStatusList();
  }

  /**
   * @inheritDoc
   */
  public function getFinalIneligibleStatusList(): array {
    return $this->info->getFinalIneligibleStatusList();
  }

  public function isApplyAction(string $action): bool {
    return $this->info->isApplyAction($action);
  }

  public function isChangeRequiredStatus(string $status): bool {
    return $this->info->isChangeRequiredStatus($status);
  }

  public function isDeleteAction(string $action): bool {
    return $this->info->isDeleteAction($action);
  }

  public function isEligibleStatus(string $status): ?bool {
    return $this->info->isEligibleStatus($status);
  }

  public function isRestoreAction(string $action): bool {
    return $this->info->isRestoreAction($action);
  }

  public function isReviewStartAction(string $action): bool {
    return $this->info->isReviewStartAction($action);
  }

  public function isReviewStatus(string $status): bool {
    return $this->info->isReviewStatus($status);
  }

  public function isSnapshotRequiredStatus(FullApplicationProcessStatus $status): bool {
    return $this->info->isSnapshotRequiredStatus($status);
  }

}
