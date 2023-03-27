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

namespace Civi\Funding\FundingCase;

use Civi\Funding\ApplicationProcess\ActionStatusInfo\ApplicationProcessActionStatusInfoInterface;
use Civi\Funding\ApplicationProcess\ApplicationProcessManager;
use Civi\Funding\Entity\ApplicationProcessEntityBundle;
use Civi\RemoteTools\Api4\Query\Comparison;
use Civi\RemoteTools\Api4\Query\CompositeCondition;

final class FundingCaseStatusDeterminer implements FundingCaseStatusDeterminerInterface {

  private ApplicationProcessManager $applicationProcessManager;

  private ApplicationProcessActionStatusInfoInterface $info;

  public function __construct(
    ApplicationProcessManager $applicationProcessManager,
    ApplicationProcessActionStatusInfoInterface $info
  ) {
    $this->applicationProcessManager = $applicationProcessManager;
    $this->info = $info;
  }

  public function getStatus(string $currentStatus, string $action): string {
    return 'approve' === $action ? 'ongoing' : $currentStatus;
  }

  public function isClosedByApplicationProcess(
    ApplicationProcessEntityBundle $applicationProcessBundle,
    string $previousStatus
  ): bool {
    $ineligibleStatusList = $this->info->getFinalIneligibleStatusList();

    return 'open' === $applicationProcessBundle->getFundingCase()->getStatus() && in_array(
      $applicationProcessBundle->getApplicationProcess()->getStatus(),
      $ineligibleStatusList,
      TRUE
    ) && 0 === $this->applicationProcessManager->countBy(
        CompositeCondition::new('AND',
          Comparison::new('funding_case_id', '=', $applicationProcessBundle->getFundingCase()->getId()),
          Comparison::new('status', 'NOT IN', $ineligibleStatusList),
        ),
      );
  }

}
