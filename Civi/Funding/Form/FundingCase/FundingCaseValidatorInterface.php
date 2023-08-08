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

namespace Civi\Funding\Form\FundingCase;

use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\Entity\FundingCaseTypeEntity;
use Civi\Funding\Entity\FundingProgramEntity;

interface FundingCaseValidatorInterface {

  public const SERVICE_TAG = 'funding.case.validator';

  /**
   * @phpstan-return array<string>
   */
  public static function getSupportedFundingCaseTypes(): array;

  /**
   * Called when updating a funding case.
   *
   * @phpstan-param array<string, mixed> $data JSON serializable.
   */
  public function validateUpdate(
    FundingProgramEntity $fundingProgram,
    FundingCaseTypeEntity $fundingCaseType,
    FundingCaseEntity $fundingCase,
    array $data,
    int $maxErrors = 1
  ): FundingCaseValidationResult;

  /**
   * Called when creating a new funding case.
   *
   * @phpstan-param array<string, mixed> $data JSON serializable.
   */
  public function validateNew(
    int $contactId,
    FundingProgramEntity $fundingProgram,
    FundingCaseTypeEntity $fundingCaseType,
    array $data,
    int $maxErrors = 1
  ): FundingCaseValidationResult;

}
