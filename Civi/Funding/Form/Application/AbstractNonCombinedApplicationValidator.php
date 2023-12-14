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

namespace Civi\Funding\Form\Application;

use Civi\Funding\ApplicationProcess\JsonSchema\Validator\ApplicationSchemaValidationResult;
use Civi\Funding\ApplicationProcess\JsonSchema\Validator\ApplicationSchemaValidatorInterface;
use Civi\Funding\Entity\FundingCaseTypeEntity;
use Civi\Funding\Entity\FundingProgramEntity;
use Civi\RemoteTools\JsonSchema\JsonSchema;

/**
 * @property NonCombinedApplicationJsonSchemaFactoryInterface $jsonSchemaFactory
 */
// phpcs:disable Generic.Files.LineLength.TooLong
abstract class AbstractNonCombinedApplicationValidator extends AbstractApplicationValidator implements NonCombinedApplicationValidatorInterface {
// phpcs:enable
  public function __construct(
    NonCombinedApplicationJsonSchemaFactoryInterface $jsonSchemaFactory,
    ApplicationSchemaValidatorInterface $jsonSchemaValidator
  ) {
    parent::__construct($jsonSchemaFactory, $jsonSchemaValidator);
  }

  /**
   * @inheritDoc
   */
  public function validateInitial(
    int $contactId,
    FundingProgramEntity $fundingProgram,
    FundingCaseTypeEntity $fundingCaseType,
    array $data,
    int $maxErrors = 1
  ): ApplicationValidationResult {
    $jsonSchema = $this->jsonSchemaFactory->createJsonSchemaInitial($contactId, $fundingCaseType, $fundingProgram);
    $jsonSchemaValidationResult = $this->jsonSchemaValidator->validate($jsonSchema, $data, $maxErrors);
    if (!$jsonSchemaValidationResult->isValid()) {
      return ApplicationValidationResult::newInvalid(
        // @phpstan-ignore-next-line leaf error messages are not empty.
        $jsonSchemaValidationResult->getLeafErrorMessages(),
        new ValidatedApplicationDataInvalid($jsonSchemaValidationResult->getData()),
      );
    }

    return $this->getValidationResultInitial(
      $contactId,
      $fundingProgram,
      $fundingCaseType,
      $data,
      $jsonSchema,
      $jsonSchemaValidationResult,
      $maxErrors,
    );
  }

  /**
   * Called after successful JSON schema validation.
   *
   * @phpstan-param array<string, mixed> $formData JSON serializable.
   */
  abstract protected function getValidationResultInitial(
    int $contactId,
    FundingProgramEntity $fundingProgram,
    FundingCaseTypeEntity $fundingCaseType,
    array $formData,
    JsonSchema $jsonSchema,
    ApplicationSchemaValidationResult $jsonSchemaValidationResult,
    int $maxErrors
  ): ApplicationValidationResult;

}
