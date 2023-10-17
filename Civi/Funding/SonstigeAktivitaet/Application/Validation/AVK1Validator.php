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

namespace Civi\Funding\SonstigeAktivitaet\Application\Validation;

use Civi\Funding\Entity\ApplicationProcessEntityBundle;
use Civi\Funding\Entity\FundingCaseTypeEntity;
use Civi\Funding\Entity\FundingProgramEntity;
use Civi\Funding\Form\Application\AbstractNonCombinedApplicationValidator;
use Civi\Funding\Form\Application\ApplicationValidationResult;
use Civi\Funding\SonstigeAktivitaet\Traits\AVK1SupportedFundingCaseTypesTrait;
use Civi\RemoteTools\JsonSchema\JsonSchema;

final class AVK1Validator extends AbstractNonCombinedApplicationValidator {

  use AVK1SupportedFundingCaseTypesTrait;

  /**
   * @inheritDoc
   */
  protected function getValidationResultExisting(
    ApplicationProcessEntityBundle $applicationProcessBundle,
    array $formData,
    JsonSchema $jsonSchema,
    array $validatedData,
    int $maxErrors
  ): ApplicationValidationResult {
    return $this->validateAVK1($validatedData, $jsonSchema);
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
    array $validatedData,
    int $maxErrors
  ): ApplicationValidationResult {
    return $this->validateAVK1($validatedData, $jsonSchema);
  }

  /**
   * @phpstan-param array<string, mixed> $validatedData
   */
  private function validateAVK1(
    array $validatedData,
    JsonSchema $jsonSchema
  ): ApplicationValidationResult {
    /** @phpstan-var array<array{beginn: string, ende: string}> $zeitraeume */
    $zeitraeume = &$validatedData['zeitraeume'];
    usort($zeitraeume, fn (array $a, array $b) => strcmp($a['beginn'], $b['beginn']));

    $zeitraeumeCount = count($zeitraeume);
    $errorMessages = [];
    for ($i = 1; $i < $zeitraeumeCount; ++$i) {
      if (strcmp($zeitraeume[$i]['beginn'], $zeitraeume[$i - 1]['ende']) <= 0) {
        $errorMessages['/zeitraeume'] =
          ['Die Zeiträume dürfen sich nicht überschneiden.'];
        break;
      }
    }

    if ([] !== $errorMessages) {
      return ApplicationValidationResult::newInvalid(
        $errorMessages,
        // @phpstan-ignore-next-line
        new AVK1ValidatedData($validatedData),
      );
    }

    // @phpstan-ignore-next-line
    return $this->createValidationResultValid(new AVK1ValidatedData($validatedData), $jsonSchema);
  }

}