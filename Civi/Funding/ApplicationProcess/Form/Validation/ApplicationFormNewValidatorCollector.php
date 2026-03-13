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

namespace Civi\Funding\ApplicationProcess\Form\Validation;

use Civi\Funding\ApplicationProcess\JsonSchema\Validator\ApplicationSchemaValidationResult;
use Civi\Funding\Entity\FundingCaseTypeEntity;
use Civi\Funding\Entity\FundingProgramEntity;
use Civi\Funding\FundingCaseType\AbstractFundingCaseTypeServiceCollector;

/**
 * @extends AbstractFundingCaseTypeServiceCollector<ApplicationFormNewValidatorInterface>
 *
 * @codeCoverageIgnore
 */
// phpcs:ignore Generic.Files.LineLength.TooLong
final class ApplicationFormNewValidatorCollector extends AbstractFundingCaseTypeServiceCollector implements ApplicationFormNewValidatorInterface {

  public function validateInitial(
    FundingCaseTypeEntity $fundingCaseType,
    FundingProgramEntity $fundingProgram,
    ApplicationSchemaValidationResult $schemaValidationResult,
    bool $readOnly
  ): ApplicationFormValidationResult {
    if ($this->hasService($fundingCaseType->getName())) {
      return $this
        ->getService($fundingCaseType->getName())
        ->validateInitial($fundingCaseType, $fundingProgram, $schemaValidationResult, $readOnly);
    }

    return new ApplicationFormValidationResult(
      $schemaValidationResult->getLeafErrorMessages(),
      $schemaValidationResult->getData(),
      $schemaValidationResult->getCostItemsData(),
      $schemaValidationResult->getResourcesItemsData(),
      $schemaValidationResult->getTaggedData(),
      $readOnly
    );
  }

}
