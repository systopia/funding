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

use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\Entity\FundingCaseTypeEntity;
use Civi\Funding\Entity\FundingProgramEntity;

final class TransferContractRenderCommand {

  /**
   * @phpstan-var array<\Civi\Funding\Entity\ApplicationProcessEntity>
   */
  private array $eligibleApplicationProcesses;

  private FundingCaseEntity $fundingCase;

  private FundingCaseTypeEntity $fundingCaseType;

  private FundingProgramEntity $fundingProgram;

  /**
   * @phpstan-param array<\Civi\Funding\Entity\ApplicationProcessEntity> $eligibleApplicationProcesses
   */
  public function __construct(
    array $eligibleApplicationProcesses,
    FundingCaseEntity $fundingCase,
    FundingCaseTypeEntity $fundingCaseType,
    FundingProgramEntity $fundingProgram
  ) {
    $this->eligibleApplicationProcesses = $eligibleApplicationProcesses;
    $this->fundingCase = $fundingCase;
    $this->fundingCaseType = $fundingCaseType;
    $this->fundingProgram = $fundingProgram;
  }

  /**
   * @phpstan-return array<\Civi\Funding\Entity\ApplicationProcessEntity>
   */
  public function getEligibleApplicationProcesses(): array {
    return $this->eligibleApplicationProcesses;
  }

  public function getFundingCase(): FundingCaseEntity {
    return $this->fundingCase;
  }

  public function getFundingCaseType(): FundingCaseTypeEntity {
    return $this->fundingCaseType;
  }

  public function getFundingProgram(): FundingProgramEntity {
    return $this->fundingProgram;
  }

}
