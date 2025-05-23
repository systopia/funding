<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
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

namespace Civi\Funding\FundingCaseTypes\BSH\HiHAktion\FundingCase\StatusDeterminer;

use Civi\Funding\ApplicationProcess\ApplicationProcessManager;
use Civi\Funding\Entity\ApplicationProcessEntityBundle;
use Civi\Funding\FundingCase\FundingCaseStatus;
use Civi\Funding\FundingCase\StatusDeterminer\DefaultFundingCaseStatusDeterminer;
use Civi\Funding\FundingCase\StatusDeterminer\FundingCaseStatusDeterminerInterface;
use Civi\Funding\FundingCaseTypes\BSH\HiHAktion\Application\Actions\HiHApplicationActionStatusInfo;
use Civi\Funding\FundingCaseTypes\BSH\HiHAktion\Traits\HiHSupportedFundingCaseTypesTrait;

final class HiHCaseStatusDeterminer implements FundingCaseStatusDeterminerInterface {

  use HiHSupportedFundingCaseTypesTrait;

  private DefaultFundingCaseStatusDeterminer $defaultStatusDeterminer;

  public function __construct(
    ApplicationProcessManager $applicationProcessManager,
    HiHApplicationActionStatusInfo $info
  ) {
    $this->defaultStatusDeterminer = new DefaultFundingCaseStatusDeterminer($applicationProcessManager, $info);
  }

  public function getStatus(string $currentStatus, string $action): string {
    return $this->defaultStatusDeterminer->getStatus($currentStatus, $action);
  }

  /**
   * @inheritDoc
   */
  public function getStatusOnApplicationProcessStatusChange(
    ApplicationProcessEntityBundle $applicationProcessBundle,
    string $previousStatus
  ): string {
    if ('rejected' === $previousStatus
      && in_array($applicationProcessBundle->getApplicationProcess()->getStatus(), ['applied', 'review'], TRUE)
    ) {
      return FundingCaseStatus::OPEN;
    }

    if (in_array($previousStatus, ['rejected_after_advisory', 'approved', 'approved_partial'], TRUE)
      && 'advisory' === $applicationProcessBundle->getApplicationProcess()->getStatus()
    ) {
      return FundingCaseStatus::OPEN;
    }

    return $this->defaultStatusDeterminer->getStatusOnApplicationProcessStatusChange(
      $applicationProcessBundle,
      $previousStatus
    );
  }

}
