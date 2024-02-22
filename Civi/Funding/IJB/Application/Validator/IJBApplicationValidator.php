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
    $validatedData = $jsonSchemaValidationResult->getData();
    /** @phpstan-var array<array{beginn: string, ende: string}> $zeitraeume */
    // @phpstan-ignore-next-line
    $zeitraeume = &$validatedData['grunddaten']['zeitraeume'];
    usort($zeitraeume, fn (array $a, array $b) => strcmp($a['beginn'], $b['beginn']));

    $zeitraeumeCount = count($zeitraeume);
    $errorMessages = [];
    for ($i = 1; $i < $zeitraeumeCount; ++$i) {
      if (strcmp($zeitraeume[$i]['beginn'], $zeitraeume[$i - 1]['ende']) <= 0) {
        $errorMessages['/grunddaten/zeitraeume'] =
          ['Die Zeiträume dürfen sich nicht überschneiden.'];
        break;
      }
    }

    if ([] !== $errorMessages) {
      return ApplicationValidationResult::newInvalid(
        $errorMessages,
        new IJBApplicationValidatedData(
          // @phpstan-ignore-next-line
          $validatedData,
          $jsonSchemaValidationResult->getCostItemsData(),
          $jsonSchemaValidationResult->getResourcesItemsData()
        ),
      );
    }

    return $this->createValidationResultValid(
      new IJBApplicationValidatedData(
        // @phpstan-ignore-next-line
        $validatedData,
        $jsonSchemaValidationResult->getCostItemsData(),
        $jsonSchemaValidationResult->getResourcesItemsData()
      ),
      $jsonSchema
    );
  }

}
