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

namespace Civi\Funding\Form\FundingCase;

use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\Entity\FundingCaseTypeEntity;
use Civi\Funding\Entity\FundingProgramEntity;
use Civi\RemoteTools\JsonSchema\JsonSchema;
use Civi\RemoteTools\JsonSchema\Validation\ValidationResultInterface;
use Civi\RemoteTools\JsonSchema\Validation\ValidatorInterface;

abstract class AbstractFundingCaseValidator implements FundingCaseValidatorInterface {

  protected FundingCaseJsonSchemaFactoryInterface $jsonSchemaFactory;

  protected ValidatorInterface $jsonSchemaValidator;

  public function __construct(
    FundingCaseJsonSchemaFactoryInterface $jsonSchemaFactory,
    ValidatorInterface $jsonSchemaValidator
  ) {
    $this->jsonSchemaFactory = $jsonSchemaFactory;
    $this->jsonSchemaValidator = $jsonSchemaValidator;
  }

  /**
   * @inheritDoc
   */
  public function validateUpdate(
    FundingProgramEntity $fundingProgram,
    FundingCaseTypeEntity $fundingCaseType,
    FundingCaseEntity $fundingCase,
    array $data,
    int $maxErrors = 1
  ): FundingCaseValidationResult {
    $jsonSchema = $this->jsonSchemaFactory->createJsonSchemaUpdate(
      $fundingProgram,
      $fundingCaseType,
      $fundingCase,
    );
    $jsonSchemaValidationResult = $this->jsonSchemaValidator->validate($jsonSchema, $data, $maxErrors);
    if (!$jsonSchemaValidationResult->isValid()) {
      return FundingCaseValidationResult::newInvalid(
      // @phpstan-ignore-next-line leaf error messages are not empty.
        $jsonSchemaValidationResult->getLeafErrorMessages(),
        new ValidatedFundingCaseDataInvalid($jsonSchemaValidationResult->getData()),
      );
    }

    return $this->getValidationResultExisting(
      $fundingProgram,
      $fundingCaseType,
      $fundingCase,
      $data,
      $jsonSchema,
      $jsonSchemaValidationResult,
      $maxErrors
    );
  }

  /**
   * @inheritDoc
   */
  public function validateNew(
    int $contactId,
    FundingProgramEntity $fundingProgram,
    FundingCaseTypeEntity $fundingCaseType,
    array $data,
    int $maxErrors = 1
  ): FundingCaseValidationResult {
    $jsonSchema = $this->jsonSchemaFactory->createJsonSchemaNew(
      $contactId,
      $fundingProgram,
      $fundingCaseType,
    );
    $jsonSchemaValidationResult = $this->jsonSchemaValidator->validate($jsonSchema, $data, $maxErrors);
    if (!$jsonSchemaValidationResult->isValid()) {
      return FundingCaseValidationResult::newInvalid(
      // @phpstan-ignore-next-line leaf error messages are not empty.
        $jsonSchemaValidationResult->getLeafErrorMessages(),
        new ValidatedFundingCaseDataInvalid($jsonSchemaValidationResult->getData()),
      );
    }

    return $this->getValidationResultNew(
      $fundingProgram,
      $fundingCaseType,
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
    FundingProgramEntity $fundingProgram,
    FundingCaseTypeEntity $fundingCaseType,
    FundingCaseEntity $fundingCase,
    array $formData,
    JsonSchema $jsonSchema,
    ValidationResultInterface $jsonSchemaValidationResult,
    int $maxErrors
  ): FundingCaseValidationResult;

  /**
   * Called after successful JSON schema validation.
   *
   * @phpstan-param array<string, mixed> $formData JSON serializable.
   */
  abstract protected function getValidationResultNew(
    FundingProgramEntity $fundingProgram,
    FundingCaseTypeEntity $fundingCaseType,
    array $formData,
    JsonSchema $jsonSchema,
    ValidationResultInterface $jsonSchemaValidationResult,
    int $maxErrors
  ): FundingCaseValidationResult;

}
