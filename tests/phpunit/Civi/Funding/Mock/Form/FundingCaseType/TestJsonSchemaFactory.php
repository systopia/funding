<?php
/*
 * Copyright (C) 2022 SYSTOPIA GmbH
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

namespace Civi\Funding\Mock\Form\FundingCaseType;

use Civi\Funding\Entity\ApplicationProcessEntity;
use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\Entity\FundingCaseTypeEntity;
use Civi\Funding\Entity\FundingProgramEntity;
use Civi\Funding\Form\ApplicationJsonSchemaFactoryInterface;
use Civi\Funding\Form\ValidatedApplicationDataInterface;
use Civi\Funding\Form\Validation\ValidationResult;
use Civi\RemoteTools\Form\JsonSchema\JsonSchema;
use Civi\RemoteTools\Form\JsonSchema\JsonSchemaInteger;
use Civi\RemoteTools\Form\JsonSchema\JsonSchemaString;

class TestJsonSchemaFactory implements ApplicationJsonSchemaFactoryInterface {

  public static function getSupportedFundingCaseTypes(): array {
    return ['TestCaseType'];
  }

  public function createValidatedData(
    ApplicationProcessEntity $applicationProcess,
    FundingCaseTypeEntity $fundingCaseType,
    ValidationResult $validationResult
  ): ValidatedApplicationDataInterface {
    return new TestValidatedData($validationResult->getData());
  }

  public function createNewValidatedData(
    FundingCaseTypeEntity $fundingCaseType,
    ValidationResult $validationResult
  ): ValidatedApplicationDataInterface {
    return new TestValidatedData($validationResult->getData());
  }

  public function createJsonSchemaExisting(
    ApplicationProcessEntity $applicationProcess,
    FundingCaseEntity $fundingCase,
    FundingCaseTypeEntity $fundingCaseType,
    FundingProgramEntity $fundingProgram
  ): JsonSchema {
    $submitActions = ['save'];
    $extraProperties = [
      'applicationProcessId' => new JsonSchemaInteger(['const' => $applicationProcess->getId(), 'readOnly' => TRUE]),
      'action' => new JsonSchemaString(['enum' => $submitActions]),
    ];
    $extraKeywords = ['required' => array_keys($extraProperties)];

    return new TestJsonSchema($extraProperties, $extraKeywords);
  }

  public function createJsonSchemaInitial(
    int $contactId,
    FundingCaseTypeEntity $fundingCaseType,
    FundingProgramEntity $fundingProgram
  ): JsonSchema {
    $submitActions = ['save'];
    $extraProperties = [
      'fundingCaseTypeId' => new JsonSchemaInteger(['const' => $fundingCaseType->getId(), 'readOnly' => TRUE]),
      'fundingProgramId' => new JsonSchemaInteger(['const' => $fundingProgram->getId(), 'readOnly' => TRUE]),
      'action' => new JsonSchemaString(['enum' => $submitActions]),
    ];
    $extraKeywords = ['required' => array_keys($extraProperties)];

    return new TestJsonSchema($extraProperties, $extraKeywords);
  }

}
