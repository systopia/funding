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
use Civi\Funding\Entity\ApplicationProcessEntityBundle;
use Civi\RemoteTools\JsonSchema\JsonSchema;

abstract class AbstractApplicationValidator implements ApplicationValidatorInterface {

  protected ApplicationJsonSchemaFactoryInterface $jsonSchemaFactory;

  protected ApplicationSchemaValidatorInterface $jsonSchemaValidator;

  public function __construct(
    ApplicationJsonSchemaFactoryInterface $jsonSchemaFactory,
    ApplicationSchemaValidatorInterface $jsonSchemaValidator
  ) {
    $this->jsonSchemaFactory = $jsonSchemaFactory;
    $this->jsonSchemaValidator = $jsonSchemaValidator;
  }

  /**
   * @inheritDoc
   */
  public function validateExisting(
    ApplicationProcessEntityBundle $applicationProcessBundle,
    array $applicationProcessStatusList,
    array $data,
    int $maxErrors = 1
  ): ApplicationValidationResult {
    $jsonSchema = $this->jsonSchemaFactory->createJsonSchemaExisting(
      $applicationProcessBundle,
      $applicationProcessStatusList
    );
    $jsonSchemaValidationResult = $this->jsonSchemaValidator->validate($jsonSchema, $data, $maxErrors);
    if (!$jsonSchemaValidationResult->isValid()) {
      return ApplicationValidationResult::newInvalid(
        // @phpstan-ignore-next-line leaf error messages are not empty.
        $jsonSchemaValidationResult->getLeafErrorMessages(),
        new ValidatedApplicationDataInvalid($jsonSchemaValidationResult->getData()),
      );
    }

    return $this->getValidationResultExisting(
      $applicationProcessBundle,
      $data,
      $jsonSchema,
      $jsonSchemaValidationResult,
      $maxErrors
    );
  }

  /**
   * Called after successful JSON schema validation.
   *
   * @phpstan-param array<string, mixed> $formData JSON serializable.
   */
  abstract protected function getValidationResultExisting(
    ApplicationProcessEntityBundle $applicationProcessBundle,
    array $formData,
    JsonSchema $jsonSchema,
    ApplicationSchemaValidationResult $jsonSchemaValidationResult,
    int $maxErrors
  ): ApplicationValidationResult;

  protected function createValidationResultValid(
    ValidatedApplicationDataInterface $validatedApplicationData,
    JsonSchema $jsonSchema
  ): ApplicationValidationResult {
    return ApplicationValidationResult::newValid($validatedApplicationData, $this->isJsonSchemaReadOnly($jsonSchema));
  }

  protected function isJsonSchemaReadOnly(JsonSchema $jsonSchema): bool {
    return TRUE === $jsonSchema->getKeywordValueOrDefault('readOnly', FALSE);
  }

}
