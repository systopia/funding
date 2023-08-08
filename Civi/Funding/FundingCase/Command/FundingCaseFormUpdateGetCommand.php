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

use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\Entity\FundingCaseTypeEntity;
use Civi\Funding\Entity\FundingProgramEntity;

final class FundingCaseFormUpdateGetCommand {

  private int $contactId;

  private FundingProgramEntity $fundingProgram;

  private FundingCaseTypeEntity $fundingCaseType;

  private FundingCaseEntity $fundingCase;

  public function __construct(
    int $contactId,
    FundingProgramEntity $fundingProgram,
    FundingCaseTypeEntity $fundingCaseType,
    FundingCaseEntity $fundingCase
  ) {
    $this->contactId = $contactId;
    $this->fundingProgram = $fundingProgram;
    $this->fundingCaseType = $fundingCaseType;
    $this->fundingCase = $fundingCase;
  }

  public function getContactId(): int {
    return $this->contactId;
  }

  public function getFundingProgram(): FundingProgramEntity {
    return $this->fundingProgram;
  }

  public function getFundingCaseType(): FundingCaseTypeEntity {
    return $this->fundingCaseType;
  }

  public function getFundingCase(): FundingCaseEntity {
    return $this->fundingCase;
  }

}
