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

namespace Civi\Funding\ApplicationProcess\Command;

use Civi\Funding\Entity\FundingCaseTypeEntity;
use Civi\Funding\Entity\FundingProgramEntity;

final class ApplicationFormNewSubmitCommand {

  private FundingCaseTypeEntity $fundingCaseType;

  private FundingProgramEntity $fundingProgram;

  /**
   * @var array<string, mixed> JSON serializable.
   */
  private array $data;

  /**
   * @param array<string, mixed> $data JSON serializable.
   */
  public function __construct(
    FundingCaseTypeEntity $fundingCaseType,
    FundingProgramEntity $fundingProgram,
    array $data
  ) {
    $this->fundingCaseType = $fundingCaseType;
    $this->fundingProgram = $fundingProgram;
    $this->data = $data;
  }

  public function getFundingProgram(): FundingProgramEntity {
    return $this->fundingProgram;
  }

  public function getFundingCaseType(): FundingCaseTypeEntity {
    return $this->fundingCaseType;
  }

  /**
   * @phpstan-return array<string, mixed> JSON serializable.
   */
  public function getData(): array {
    return $this->data;
  }

}
