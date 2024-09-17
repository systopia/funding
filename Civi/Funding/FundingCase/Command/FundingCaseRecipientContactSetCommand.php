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

namespace Civi\Funding\FundingCase\Command;

use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\Entity\FundingCaseTypeEntity;
use Civi\Funding\Entity\FundingProgramEntity;

/**
 * @codeCoverageIgnore
 */
final class FundingCaseRecipientContactSetCommand {

  /**
   * @phpstan-var array<int, \Civi\Funding\Entity\FullApplicationProcessStatus>
   */
  private array $applicationProcessStatusList;

  private FundingCaseEntity $fundingCase;

  private int $recipientContactId;

  private FundingCaseTypeEntity $fundingCaseType;

  private FundingProgramEntity $fundingProgram;

  /**
   * @phpstan-param array<int, \Civi\Funding\Entity\FullApplicationProcessStatus> $applicationProcessStatusList
   *   Indexed by application process ID.
   */
  public function __construct(
    FundingCaseEntity $fundingCase,
    int $recipientContactId,
    array $applicationProcessStatusList,
    FundingCaseTypeEntity $fundingCaseType,
    FundingProgramEntity $fundingProgram
  ) {
    $this->fundingCase = $fundingCase;
    $this->recipientContactId = $recipientContactId;
    $this->applicationProcessStatusList = $applicationProcessStatusList;
    $this->fundingCaseType = $fundingCaseType;
    $this->fundingProgram = $fundingProgram;
  }

  /**
   * @phpstan-return array<int, \Civi\Funding\Entity\FullApplicationProcessStatus>
   *    Indexed by application process ID.
   */
  public function getApplicationProcessStatusList(): array {
    return $this->applicationProcessStatusList;
  }

  public function getFundingCase(): FundingCaseEntity {
    return $this->fundingCase;
  }

  public function getRecipientContactId(): int {
    return $this->recipientContactId;
  }

  public function getFundingCaseType(): FundingCaseTypeEntity {
    return $this->fundingCaseType;
  }

  public function getFundingProgram(): FundingProgramEntity {
    return $this->fundingProgram;
  }

}
