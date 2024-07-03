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
use Civi\Funding\Form\FundingCase\ValidatedFundingCaseData;
use Civi\Funding\SammelantragKurs\Traits\KursSupportedFundingCaseTypesTrait;
use Civi\RemoteTools\JsonSchema\JsonSchema;
use Civi\RemoteTools\JsonSchema\Validation\ValidationResult;

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
    ValidationResult $jsonSchemaValidationResult,
    int $maxErrors
  ): FundingCaseValidationResult {
    return FundingCaseValidationResult::newValid(new ValidatedFundingCaseData(
      $jsonSchemaValidationResult->getData(),
      $jsonSchemaValidationResult->getTaggedData()
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
    ValidationResult $jsonSchemaValidationResult,
    int $maxErrors
  ): FundingCaseValidationResult {
    return FundingCaseValidationResult::newValid(new ValidatedFundingCaseData(
      $jsonSchemaValidationResult->getData(),
      $jsonSchemaValidationResult->getTaggedData()
    ));
  }

}
