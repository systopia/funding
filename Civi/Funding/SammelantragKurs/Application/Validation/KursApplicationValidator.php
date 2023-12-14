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

    $validatedApplicationData = new KursApplicationValidatedData(
      // @phpstan-ignore-next-line
      $validatedData,
      $jsonSchemaValidationResult->getCostItemsData(),
      $fundingCase->getRecipientContactId()
    );

    if ([] !== $errorMessages) {
      return ApplicationValidationResult::newInvalid($errorMessages, $validatedApplicationData);
    }

    return $this->createValidationResultValid($validatedApplicationData, $jsonSchema);
  }

}
