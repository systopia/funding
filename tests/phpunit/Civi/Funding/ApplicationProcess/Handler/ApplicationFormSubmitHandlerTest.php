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
use Civi\Funding\ApplicationProcess\Command\ApplicationFormCommentPersistCommand;
use Civi\Funding\ApplicationProcess\Command\ApplicationFormSubmitCommand;
use Civi\Funding\ApplicationProcess\StatusDeterminer\ApplicationProcessStatusDeterminerInterface;
use Civi\Funding\Entity\FullApplicationProcessStatus;
use Civi\Funding\EntityFactory\ApplicationProcessBundleFactory;
use Civi\Funding\EntityFactory\ApplicationProcessFactory;
use Civi\Funding\Form\ApplicationJsonSchemaFactoryInterface;
use Civi\Funding\Form\Validation\ValidationResult;
use Civi\Funding\Form\Validation\ValidatorInterface;
use Civi\Funding\Form\ValidationErrorFactory;
use Civi\Funding\Mock\Form\ValidatedApplicationDataMock;
use Civi\RemoteTools\Form\JsonSchema\JsonSchema;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Systopia\JsonSchema\Errors\ErrorCollector;

/**
 * @covers \Civi\Funding\ApplicationProcess\Handler\ApplicationFormSubmitHandler
 * @covers \Civi\Funding\ApplicationProcess\Command\ApplicationFormSubmitCommand
 * @covers \Civi\Funding\ApplicationProcess\Command\ApplicationFormSubmitResult
 */
final class ApplicationFormSubmitHandlerTest extends TestCase {

  /**
   * @var \Civi\Funding\ApplicationProcess\ApplicationProcessManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $applicationProcessManagerMock;

  /**
   * @var \Civi\Funding\ApplicationProcess\Handler\ApplicationFormCommentPersistHandlerInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $commentStoreHandlerMock;

  private ApplicationFormSubmitHandler $handler;

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
    $this->commentStoreHandlerMock = $this->createMock(ApplicationFormCommentPersistHandlerInterface::class);
    $this->jsonSchemaFactoryMock = $this->createMock(ApplicationJsonSchemaFactoryInterface::class);
    $this->statusDeterminerMock = $this->createMock(ApplicationProcessStatusDeterminerInterface::class);
    $this->validatorMock = $this->createMock(ValidatorInterface::class);
    $this->handler = new ApplicationFormSubmitHandler(
      $this->applicationProcessManagerMock,
      $this->commentStoreHandlerMock,
      $this->jsonSchemaFactoryMock,
      $this->statusDeterminerMock,
      $this->validatorMock
    );
  }

  public function testHandleValid(): void {
    $command = $this->createCommand();
    $jsonSchema = new JsonSchema([]);
    $this->mockCreateJsonSchema($command, $jsonSchema);

    $validationResult = new ValidationResult([], new ErrorCollector());
    $this->mockValidator($jsonSchema, $command->getData(), $validationResult);

    $validatedData = new ValidatedApplicationDataMock();
    $this->mockCreateValidatedData($command, $validationResult, $validatedData);

    $newStatus = new FullApplicationProcessStatus('new_status', TRUE, FALSE);
    $this->statusDeterminerMock->method('getStatus')
      ->with($command->getApplicationProcess()->getFullStatus(), ValidatedApplicationDataMock::ACTION)
      ->willReturn($newStatus);

    $this->applicationProcessManagerMock->expects(static::once())->method('update')
      ->with($command->getContactId(), $command->getApplicationProcessBundle());

    $this->commentStoreHandlerMock->expects(static::never())->method('handle');

    $result = $this->handler->handle($command);

    static::assertTrue($result->isSuccess());
    static::assertSame($validationResult, $result->getValidationResult());
    static::assertSame($validatedData, $result->getValidatedData());

    $applicationProcess = $command->getApplicationProcess();
    static::assertSame(ValidatedApplicationDataMock::TITLE, $applicationProcess->getTitle());
    static::assertSame(ValidatedApplicationDataMock::SHORT_DESCRIPTION, $applicationProcess->getShortDescription());
    static::assertEquals(new \DateTime(ValidatedApplicationDataMock::START_DATE),
      $applicationProcess->getStartDate());
    static::assertEquals(new \DateTime(ValidatedApplicationDataMock::END_DATE),
      $applicationProcess->getEndDate());
    static::assertSame(ValidatedApplicationDataMock::AMOUNT_REQUESTED, $applicationProcess->getAmountRequested());
    static::assertSame(ValidatedApplicationDataMock::APPLICATION_DATA, $applicationProcess->getRequestData());
    static::assertSame('new_status', $applicationProcess->getStatus());
    static::assertTrue($applicationProcess->getIsReviewCalculative());
    static::assertFalse($applicationProcess->getIsReviewContent());
  }

  public function testHandleComment(): void {
    $command = $this->createCommand();
    $jsonSchema = new JsonSchema([]);
    $this->mockCreateJsonSchema($command, $jsonSchema);

    $validationResult = new ValidationResult([], new ErrorCollector());
    $this->mockValidator($jsonSchema, $command->getData(), $validationResult);

    $validatedData = new ValidatedApplicationDataMock([], ['comment' => 'test']);
    $this->mockCreateValidatedData($command, $validationResult, $validatedData);

    $newStatus = new FullApplicationProcessStatus('new_status', TRUE, FALSE);
    $this->statusDeterminerMock->method('getStatus')->willReturn($newStatus);

    $this->applicationProcessManagerMock->expects(static::once())->method('update');

    $this->commentStoreHandlerMock->expects(static::once())->method('handle')
      ->with(new ApplicationFormCommentPersistCommand(
        $command->getContactId(),
        $command->getApplicationProcess(),
        $command->getFundingCase(),
        $command->getFundingCaseType(),
        $command->getFundingProgram(),
        $validatedData
      ));

    $result = $this->handler->handle($command);

    static::assertTrue($result->isSuccess());
  }

  public function testHandleValidReadOnly(): void {
    $command = $this->createCommand();
    $jsonSchema = new JsonSchema(['readOnly' => TRUE]);
    $this->mockCreateJsonSchema($command, $jsonSchema);

    $validationResult = new ValidationResult([], new ErrorCollector());
    $this->mockValidator($jsonSchema, $command->getData(), $validationResult);

    $validatedData = new ValidatedApplicationDataMock([], ['action' => 'modify']);
    $this->mockCreateValidatedData($command, $validationResult, $validatedData);

    $newStatus = new FullApplicationProcessStatus('new_status', TRUE, FALSE);
    $this->statusDeterminerMock->method('getStatus')
      ->with($command->getApplicationProcess()->getFullStatus(), 'modify')
      ->willReturn($newStatus);

    $this->applicationProcessManagerMock->expects(static::once())->method('update')
      ->with($command->getContactId(), $command->getApplicationProcessBundle());

    $this->commentStoreHandlerMock->expects(static::never())->method('handle');

    $result = $this->handler->handle($command);

    static::assertTrue($result->isSuccess());
    static::assertSame($validationResult, $result->getValidationResult());
    static::assertSame($validatedData, $result->getValidatedData());

    // only status should be changed because form is read only
    $expectedApplicationProcess = ApplicationProcessFactory::createApplicationProcess([
      'status' => 'new_status',
      'is_review_calculative' => TRUE,
      'is_review_content' => FALSE,
    ]);
    static::assertEquals($expectedApplicationProcess, $command->getApplicationProcess());
  }

  public function testHandleValidDelete(): void {
    $command = $this->createCommand();
    $jsonSchema = new JsonSchema([]);
    $this->mockCreateJsonSchema($command, $jsonSchema);

    $validationResult = new ValidationResult([], new ErrorCollector());
    $this->mockValidator($jsonSchema, $command->getData(), $validationResult);

    $validatedData = new ValidatedApplicationDataMock([], ['action' => 'delete']);
    $this->mockCreateValidatedData($command, $validationResult, $validatedData);

    $this->applicationProcessManagerMock->expects(static::once())->method('delete')
      ->with($command->getApplicationProcessBundle());

    $this->commentStoreHandlerMock->expects(static::never())->method('handle');

    $result = $this->handler->handle($command);

    static::assertTrue($result->isSuccess());
    static::assertSame($validationResult, $result->getValidationResult());
    static::assertSame($validatedData, $result->getValidatedData());
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

    $this->commentStoreHandlerMock->expects(static::never())->method('handle');

    $result = $this->handler->handle($command);

    static::assertFalse($result->isSuccess());
    static::assertSame($validationResult, $result->getValidationResult());
    static::assertNull($result->getValidatedData());
  }

  private function createCommand(): ApplicationFormSubmitCommand {
    return new ApplicationFormSubmitCommand(
      1,
      ApplicationProcessBundleFactory::createApplicationProcessBundle(),
      ['test' => 'foo'],
    );
  }

  private function mockCreateJsonSchema(ApplicationFormSubmitCommand $command, JsonSchema $jsonSchema): void {
    $this->jsonSchemaFactoryMock->expects(static::once())->method('createJsonSchemaExisting')
      ->with(
        $command->getApplicationProcessBundle(),
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

  private function mockCreateValidatedData(
    ApplicationFormSubmitCommand $command,
    ValidationResult $validationResult,
    ValidatedApplicationDataMock $validatedData
  ): void {
    $this->jsonSchemaFactoryMock->expects(static::once())->method('createValidatedData')->with(
      $command->getApplicationProcess(),
      $command->getFundingCaseType(),
      $validationResult
    )->willReturn($validatedData);
  }

}
