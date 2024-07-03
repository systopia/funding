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

namespace Civi\Funding\SonstigeAktivitaet\Validation;

use Civi\Funding\ApplicationProcess\JsonSchema\Validator\ApplicationSchemaValidationResult;
use Civi\Funding\ApplicationProcess\JsonSchema\Validator\ApplicationSchemaValidatorInterface;
use Civi\Funding\EntityFactory\ApplicationProcessBundleFactory;
use Civi\Funding\EntityFactory\FundingCaseTypeFactory;
use Civi\Funding\EntityFactory\FundingProgramFactory;
use Civi\Funding\Form\Application\NonCombinedApplicationJsonSchemaFactoryInterface;
use Civi\Funding\Form\Application\ValidatedApplicationData;
use Civi\Funding\SonstigeAktivitaet\Application\Validation\AVK1Validator;
use Civi\RemoteTools\JsonSchema\JsonSchema;
use Civi\RemoteTools\JsonSchema\Validation\ValidationResult;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Systopia\JsonSchema\Errors\ErrorCollector;
use Systopia\JsonSchema\Tags\TaggedDataContainer;

/**
 * @covers \Civi\Funding\SonstigeAktivitaet\Application\Validation\AVK1Validator
 */
final class AVK1ValidatorTest extends TestCase {

  /**
   * @var \Civi\Funding\Form\Application\NonCombinedApplicationJsonSchemaFactoryInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $jsonSchemaFactoryMock;

  /**
   * @var \Civi\Funding\ApplicationProcess\JsonSchema\Validator\ApplicationSchemaValidatorInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $jsonSchemaValidatorMock;

  private AVK1Validator $validator;

  protected function setUp(): void {
    parent::setUp();
    $this->jsonSchemaFactoryMock = $this->createMock(NonCombinedApplicationJsonSchemaFactoryInterface::class);
    $this->jsonSchemaValidatorMock = $this->createMock(ApplicationSchemaValidatorInterface::class);
    $this->validator = new AVK1Validator(
      $this->jsonSchemaFactoryMock,
      $this->jsonSchemaValidatorMock
    );
  }

  public function testGetSupportedFundingCaseTypes(): void {
    static::assertSame(['AVK1SonstigeAktivitaet'], $this->validator::getSupportedFundingCaseTypes());
  }

  public function testValidateExisting(): void {
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle();
    $formData = ['foo' => 'bar'];
    $jsonSchemaValidatedData = [
      '_action' => 'save',
      'grunddaten' => ['titel' => 'Test'],
    ];

    $jsonSchema = new JsonSchema([]);
    $this->jsonSchemaFactoryMock->method('createJsonSchemaExisting')
      ->with($applicationProcessBundle)
      ->willReturn($jsonSchema);
    $this->jsonSchemaValidatorMock->method('validate')
      ->with($jsonSchema, $formData, 2)
      ->willReturn(new ApplicationSchemaValidationResult(
        new ValidationResult($jsonSchemaValidatedData, new TaggedDataContainer(), new ErrorCollector()),
        [],
        []
      ));

    $validationResult = $this->validator->validateExisting($applicationProcessBundle, [], $formData, 2);
    static::assertSame([], $validationResult->getErrorMessages());
    static::assertTrue($validationResult->isValid());
    static::assertInstanceOf(ValidatedApplicationData::class, $validationResult->getValidatedData());
  }

  public function testValidateInitial(): void {
    $formData = ['foo' => 'bar'];
    $jsonSchemaValidatedData = [
      '_action' => 'save',
      'grunddaten' => ['titel' => 'test'],
    ];

    $contactId = 12;
    $fundingProgram = FundingProgramFactory::createFundingProgram();
    $fundingCaseType = FundingCaseTypeFactory::createFundingCaseType();
    $jsonSchema = new JsonSchema([]);
    $this->jsonSchemaFactoryMock->method('createJsonSchemaInitial')
      ->with($contactId, $fundingCaseType, $fundingProgram)
      ->willReturn($jsonSchema);
    $this->jsonSchemaValidatorMock->method('validate')
      ->with($jsonSchema, $formData, 2)
      ->willReturn(new ApplicationSchemaValidationResult(
        new ValidationResult($jsonSchemaValidatedData, new TaggedDataContainer(), new ErrorCollector()),
        [],
        []
      ));

    $validationResult = $this->validator->validateInitial($contactId, $fundingProgram, $fundingCaseType, $formData, 2);
    static::assertSame([], $validationResult->getErrorMessages());
    static::assertTrue($validationResult->isValid());
    static::assertInstanceOf(ValidatedApplicationData::class, $validationResult->getValidatedData());
  }

}
