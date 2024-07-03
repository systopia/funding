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

namespace Civi\Funding\SammelantragKurs\Application\Validation;

use Civi\Funding\ApplicationProcess\JsonSchema\Validator\ApplicationSchemaValidationResult;
use Civi\Funding\Entity\ApplicationProcessEntityBundle;
use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\Entity\FundingCaseTypeEntity;
use Civi\Funding\Entity\FundingProgramEntity;
use Civi\Funding\Form\Application\AbstractCombinedApplicationValidator;
use Civi\Funding\Form\Application\ApplicationValidationResult;
use Civi\Funding\Form\Application\ValidatedApplicationData;
use Civi\Funding\SammelantragKurs\Traits\KursSupportedFundingCaseTypesTrait;
use Civi\RemoteTools\JsonSchema\JsonSchema;

final class KursApplicationValidator extends AbstractCombinedApplicationValidator {

  use KursSupportedFundingCaseTypesTrait;

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
    return $this->validateKurs($applicationProcessBundle->getFundingCase(), $jsonSchemaValidationResult, $jsonSchema);
  }

  /**
   * @inheritDoc
   */
  protected function getValidationResultAdd(
    FundingProgramEntity $fundingProgram,
    FundingCaseTypeEntity $fundingCaseType,
    FundingCaseEntity $fundingCase,
    array $formData,
    JsonSchema $jsonSchema,
    ApplicationSchemaValidationResult $jsonSchemaValidationResult,
    int $maxErrors
  ): ApplicationValidationResult {
    return $this->validateKurs($fundingCase, $jsonSchemaValidationResult, $jsonSchema);
  }

  private function validateKurs(
    FundingCaseEntity $fundingCase,
    ApplicationSchemaValidationResult $jsonSchemaValidationResult,
    JsonSchema $jsonSchema
  ): ApplicationValidationResult {
    return $this->createValidationResultValid(
      new ValidatedApplicationData(
        $jsonSchemaValidationResult->getData(),
        $jsonSchemaValidationResult->getCostItemsData(),
        $jsonSchemaValidationResult->getResourcesItemsData(),
        $jsonSchemaValidationResult->getTaggedData(),
      ),
      $jsonSchema
    );
  }

}
