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

namespace Civi\Funding\TransferContract\Command;

use Civi\Funding\Entity\FundingCaseBundle;
use Civi\Funding\Entity\Traits\FundingCaseBundleTrait;

final class TransferContractRenderCommand {

  use FundingCaseBundleTrait;

  /**
   * @phpstan-var array<\Civi\Funding\Entity\ApplicationProcessEntity>
   */
  private array $eligibleApplicationProcesses;

  /**
   * @phpstan-param array<\Civi\Funding\Entity\ApplicationProcessEntity> $eligibleApplicationProcesses
   */
  public function __construct(
    array $eligibleApplicationProcesses,
    FundingCaseBundle $fundingCaseBundle,
  ) {
    $this->eligibleApplicationProcesses = $eligibleApplicationProcesses;
    $this->fundingCaseBundle = $fundingCaseBundle;
  }

  /**
   * @phpstan-return array<\Civi\Funding\Entity\ApplicationProcessEntity>
   */
  public function getEligibleApplicationProcesses(): array {
    return $this->eligibleApplicationProcesses;
  }

}
