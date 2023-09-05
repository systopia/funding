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

namespace Civi\Funding\FundingCase\Command;

use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\Form\FundingCase\FundingCaseValidationResult;
use Civi\Funding\Form\FundingCase\ValidatedFundingCaseDataInterface;

final class FundingCaseFormNewSubmitResult {

  private ?FundingCaseEntity $fundingCase;

  private bool $success;

  private FundingCaseValidationResult $validationResult;

  public static function createError(FundingCaseValidationResult $validationResult): self {
    return new self(FALSE, $validationResult, NULL);
  }

  public static function createSuccess(
    FundingCaseValidationResult $validationResult,
    FundingCaseEntity $fundingCase
  ): self {
    return new self(TRUE, $validationResult, $fundingCase);
  }

  private function __construct(
    bool $success,
    FundingCaseValidationResult $validationResult,
    ?FundingCaseEntity $fundingCase
  ) {
    $this->success = $success;
    $this->validationResult = $validationResult;
    $this->fundingCase = $fundingCase;
  }

  /**
   * @return \Civi\Funding\Entity\FundingCaseEntity|null Funding case on success, NULL otherwise.
   */
  public function getFundingCase(): ?FundingCaseEntity {
    return $this->fundingCase;
  }

  public function getValidatedData(): ValidatedFundingCaseDataInterface {
    return $this->validationResult->getValidatedData();
  }

  public function getValidationResult(): FundingCaseValidationResult {
    return $this->validationResult;
  }

  public function isSuccess(): bool {
    return $this->success;
  }

}
