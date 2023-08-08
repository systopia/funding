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

namespace Civi\Funding\SammelantragKurs\FundingCase\Validation;

use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\Entity\FundingCaseTypeEntity;
use Civi\Funding\Entity\FundingProgramEntity;
use Civi\Funding\Form\FundingCase\AbstractFundingCaseValidator;
use Civi\Funding\Form\FundingCase\FundingCaseValidationResult;
use Civi\Funding\SammelantragKurs\Traits\KursSupportedFundingCaseTypesTrait;
use Civi\RemoteTools\JsonSchema\JsonSchema;

final class KursCaseValidator extends AbstractFundingCaseValidator {

  use KursSupportedFundingCaseTypesTrait;

  /**
   * @inheritDoc
   */
  protected function getValidationResultExisting(
    FundingProgramEntity $fundingProgram,
    FundingCaseTypeEntity $fundingCaseType,
    FundingCaseEntity $fundingCase,
    array $formData,
    JsonSchema $jsonSchema,
    array $validatedData,
    int $maxErrors
  ): FundingCaseValidationResult {
    return FundingCaseValidationResult::newValid(new KursCaseValidatedData(
      // @phpstan-ignore-next-line
      $validatedData,
      $fundingCase->getTitle() ?? '',
    ));
  }

  /**
   * @inheritDoc
   */
  protected function getValidationResultNew(
    FundingProgramEntity $fundingProgram,
    FundingCaseTypeEntity $fundingCaseType,
    array $formData,
    JsonSchema $jsonSchema,
    array $validatedData,
    int $maxErrors
  ): FundingCaseValidationResult {
    return FundingCaseValidationResult::newValid(new KursCaseValidatedData(
      // @phpstan-ignore-next-line
      $validatedData,
      $this->generateTitle($fundingProgram, $fundingCaseType),
    ));
  }

  private function generateTitle(
    FundingProgramEntity $fundingProgram,
    FundingCaseTypeEntity $fundingCaseType
  ): string {
    return sprintf(
      '%s-%s-%s',
      date('Y'),
      $fundingProgram->getAbbreviation(),
      $fundingCaseType->getAbbreviation(),
    );
  }

}
