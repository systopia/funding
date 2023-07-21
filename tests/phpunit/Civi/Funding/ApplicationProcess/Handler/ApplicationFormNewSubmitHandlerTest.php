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

namespace Civi\Funding\ApplicationProcess\Handler;

use Civi\Funding\ApplicationProcess\ApplicationProcessManager;
use Civi\Funding\ApplicationProcess\Command\ApplicationFormNewSubmitCommand;
use Civi\Funding\ApplicationProcess\StatusDeterminer\ApplicationProcessStatusDeterminerInterface;
use Civi\Funding\EntityFactory\ApplicationProcessFactory;
use Civi\Funding\EntityFactory\FundingCaseFactory;
use Civi\Funding\EntityFactory\FundingCaseTypeFactory;
use Civi\Funding\EntityFactory\FundingProgramFactory;
use Civi\Funding\Form\ApplicationJsonSchemaFactoryInterface;
use Civi\Funding\Form\Validation\ValidationResult;
use Civi\Funding\Form\Validation\ValidatorInterface;
use Civi\Funding\Form\ValidationErrorFactory;
use Civi\Funding\FundingCase\FundingCaseManager;
use Civi\Funding\Mock\Form\ValidatedApplicationDataMock;
use Civi\RemoteTools\Form\JsonSchema\JsonSchema;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Systopia\JsonSchema\Errors\ErrorCollector;

/**
 * @covers \Civi\Funding\ApplicationProcess\Handler\ApplicationFormNewSubmitHandler
 * @covers \Civi\Funding\ApplicationProcess\Command\ApplicationFormNewSubmitCommand
 * @covers \Civi\Funding\ApplicationProcess\Command\ApplicationFormNewSubmitResult
 * @covers \Civi\Funding\ApplicationProcess\Command\AbstractApplicationFormSubmitResult
 */
final class ApplicationFormNewSubmitHandlerTest extends TestCase {

  /**
   * @var \Civi\Funding\ApplicationProcess\ApplicationProcessManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $applicationProcessManagerMock;

  /**
   * @var \Civi\Funding\FundingCase\FundingCaseManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $fundingCaseManagerMock;

  private ApplicationFormNewSubmitHandler $handler;

  /**
   * @var \Civi\Funding\Form\ApplicationJsonSchemaFactoryInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $jsonSchemaFactoryMock;

  /**
   * @var \Civi\Funding\ApplicationProcess\StatusDeterminer\ApplicationProcessStatusDeterminerInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $statusDeterminerMock;

  /**
   * @var \Civi\Funding\Form\Validation\ValidatorInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $validatorMock;

  protected function setUp(): void {
    parent::setUp();
    $this->applicationProcessManagerMock = $this->createMock(ApplicationProcessManager::class);
    $this->jsonSchemaFactoryMock = $this->createMock(ApplicationJsonSchemaFactoryInterface::class);
    $this->fundingCaseManagerMock = $this->createMock(FundingCaseManager::class);
    $this->statusDeterminerMock = $this->createMock(ApplicationProcessStatusDeterminerInterface::class);
    $this->validatorMock = $this->createMock(ValidatorInterface::class);
    $this->handler = new ApplicationFormNewSubmitHandler(
      $this->applicationProcessManagerMock,
      $this->jsonSchemaFactoryMock,
      $this->fundingCaseManagerMock,
      $this->statusDeterminerMock,
      $this->validatorMock
    );
  }

  public function testHandle(): void {
    $command = $this->createCommand();
    $jsonSchema = new JsonSchema([]);
    $this->mockCreateJsonSchema($command, $jsonSchema);

    $validationResult = new ValidationResult([], new ErrorCollector());
    $this->mockValidator($jsonSchema, $command->getData(), $validationResult);

    $validatedData = new ValidatedApplicationDataMock();
    $this->mockCreateNewValidatedData($command, $validationResult, $validatedData);

    $this->statusDeterminerMock->expects(static::once())->method('getInitialStatus')
      ->with(ValidatedApplicationDataMock::ACTION)
      ->willReturn('test_status');

    $fundingCase = FundingCaseFactory::createFundingCase();
    $this->fundingCaseManagerMock->expects(static::once())->method('getOpenOrCreate')
      ->with($command->getContactId(), [
        'funding_program' => $command->getFundingProgram(),
        'funding_case_type' => $command->getFundingCaseType(),
        'recipient_contact_id' => ValidatedApplicationDataMock::RECIPIENT_CONTACT_ID,
      ])->willReturn($fundingCase);

    $applicationProcess = ApplicationProcessFactory::createApplicationProcess();
    $this->applicationProcessManagerMock->expects(static::once())->method('create')
      ->with(
        $command->getContactId(),
        $fundingCase,
        $command->getFundingCaseType(),
        $command->getFundingProgram(),
        'test_status',
        $validatedData
      )->willReturn($applicationProcess);

    $result = $this->handler->handle($command);

    static::assertTrue($result->isSuccess());
    static::assertSame($validationResult, $result->getValidationResult());
    static::assertSame($validatedData, $result->getValidatedData());
    static::assertNotNull($result->getApplicationProcessBundle());
    static::assertSame($applicationProcess, $result->getApplicationProcessBundle()->getApplicationProcess());
  }

  public function testHandleInvalid(): void {
    $command = $this->createCommand();
    $jsonSchema = new JsonSchema([]);
    $this->mockCreateJsonSchema($command, $jsonSchema);

    $postValidationData = ['foo' => 'baz'];
    $errorCollector = new ErrorCollector();
    $errorCollector->addError(ValidationErrorFactory::createValidationError());
    $validationResult = new ValidationResult($postValidationData, $errorCollector);
    $this->mockValidator($jsonSchema, $command->getData(), $validationResult);

    $this->applicationProcessManagerMock->expects(static::never())->method('update');

    $result = $this->handler->handle($command);

    static::assertFalse($result->isSuccess());
    static::assertSame($validationResult, $result->getValidationResult());
    static::assertNull($result->getValidatedData());
    static::assertNull($result->getApplicationProcessBundle());
  }

  private function createCommand(): ApplicationFormNewSubmitCommand {
    return new ApplicationFormNewSubmitCommand(
      1,
      FundingCaseTypeFactory::createFundingCaseType(),
      FundingProgramFactory::createFundingProgram(),
      ['test' => 'foo'],
    );
  }

  private function mockCreateJsonSchema(ApplicationFormNewSubmitCommand $command, JsonSchema $jsonSchema): void {
    $this->jsonSchemaFactoryMock->expects(static::once())->method('createJsonSchemaInitial')
      ->with(
        $command->getContactId(),
        $command->getFundingCaseType(),
        $command->getFundingProgram(),
      )->willReturn($jsonSchema);
  }

  /**
   * @phpstan-param array<string, mixed> $data
   */
  private function mockValidator(JsonSchema $jsonSchema, array $data, ValidationResult $validationResult): void {
    $this->validatorMock->expects(static::once())->method('validate')
      ->with($jsonSchema, $data)
      ->willReturn($validationResult);
  }

  private function mockCreateNewValidatedData(
    ApplicationFormNewSubmitCommand $command,
    ValidationResult $validationResult,
    ValidatedApplicationDataMock $validatedData
  ): void {
    $this->jsonSchemaFactoryMock->expects(static::once())->method('createNewValidatedData')->with(
      $command->getFundingCaseType(),
      $validationResult
    )->willReturn($validatedData);
  }

}
