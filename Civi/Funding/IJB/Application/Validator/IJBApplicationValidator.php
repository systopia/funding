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

namespace Civi\Funding\IJB\Application\Validator;

use Civi\Funding\ApplicationProcess\JsonSchema\Validator\ApplicationSchemaValidationResult;
use Civi\Funding\Entity\ApplicationProcessEntityBundle;
use Civi\Funding\Entity\FundingCaseTypeEntity;
use Civi\Funding\Entity\FundingProgramEntity;
use Civi\Funding\Form\Application\AbstractNonCombinedApplicationValidator;
use Civi\Funding\Form\Application\ApplicationValidationResult;
use Civi\Funding\Form\Application\ValidatedApplicationData;
use Civi\Funding\IJB\Traits\IJBSupportedFundingCaseTypesTrait;
use Civi\RemoteTools\JsonSchema\JsonSchema;

final class IJBApplicationValidator extends AbstractNonCombinedApplicationValidator {

  use IJBSupportedFundingCaseTypesTrait;

  /**
   * @inheritDoc
   */
  protected function getValidationResultExisting(
    ApplicationProcessEntityBundle $applicationProcessBundle,
    array $formData,
    JsonSchema $jsonSchema,
    ApplicationSchemaValidationResult $jsonSchemaValidationResult,
    int $maxErrors
  ): ApplicationValidationResult {
    return $this->validateIJB($jsonSchemaValidationResult, $jsonSchema);
  }

  /**
   * @inheritDoc
   */
  protected function getValidationResultInitial(
    int $contactId,
    FundingProgramEntity $fundingProgram,
    FundingCaseTypeEntity $fundingCaseType,
    array $formData,
    JsonSchema $jsonSchema,
    ApplicationSchemaValidationResult $jsonSchemaValidationResult,
    int $maxErrors
  ): ApplicationValidationResult {
    return $this->validateIJB($jsonSchemaValidationResult, $jsonSchema);
  }

  private function validateIJB(
    ApplicationSchemaValidationResult $jsonSchemaValidationResult,
    JsonSchema $jsonSchema
  ): ApplicationValidationResult {
    return $this->createValidationResultValid(
      new ValidatedApplicationData(
        $jsonSchemaValidationResult->getData(),
        $jsonSchemaValidationResult->getCostItemsData(),
        $jsonSchemaValidationResult->getResourcesItemsData(),
        $jsonSchemaValidationResult->getTaggedData()
      ),
      $jsonSchema
    );
  }

}
