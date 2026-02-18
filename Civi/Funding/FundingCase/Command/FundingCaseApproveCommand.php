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

namespace Civi\Funding\FundingCase\Command;

use Civi\Funding\Entity\FundingCaseBundle;
use Civi\Funding\Entity\Traits\FundingCaseBundleTrait;

/**
 * @codeCoverageIgnore
 */
final class FundingCaseApproveCommand {

  use FundingCaseBundleTrait;

  /**
   * @phpstan-var array<int, \Civi\Funding\Entity\FullApplicationProcessStatus>
   */
  private array $applicationProcessStatusList;

  private float $amount;

  /**
   * @phpstan-param array<int, \Civi\Funding\Entity\FullApplicationProcessStatus> $applicationProcessStatusList
   *   Indexed by application process ID.
   */
  public function __construct(
    FundingCaseBundle $fundingCaseBundle,
    float $amount,
    array $applicationProcessStatusList,
  ) {
    $this->fundingCaseBundle = $fundingCaseBundle;
    $this->amount = $amount;
    $this->applicationProcessStatusList = $applicationProcessStatusList;
  }

  /**
   * @phpstan-return array<int, \Civi\Funding\Entity\FullApplicationProcessStatus>
   *    Indexed by application process ID.
   */
  public function getApplicationProcessStatusList(): array {
    return $this->applicationProcessStatusList;
  }

  public function getAmount(): float {
    return $this->amount;
  }

}
