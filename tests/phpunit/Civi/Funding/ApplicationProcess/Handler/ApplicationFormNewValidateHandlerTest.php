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

use Civi\Funding\ApplicationProcess\Command\ApplicationFormNewCreateCommand;
use Civi\Funding\ApplicationProcess\Command\ApplicationFormNewValidateCommand;
use Civi\Funding\ApplicationProcess\Form\Validation\ApplicationFormNewValidatorInterface;
use Civi\Funding\ApplicationProcess\JsonSchema\Validator\ApplicationSchemaValidationResult;
use Civi\Funding\ApplicationProcess\JsonSchema\Validator\ApplicationSchemaValidatorInterface;
use Civi\Funding\EntityFactory\FundingCaseTypeFactory;
use Civi\Funding\EntityFactory\FundingProgramFactory;
use Civi\Funding\Form\Application\ValidatedApplicationDataInvalid;
use Civi\Funding\Mock\ApplicationProcess\Form\Validation\ApplicationFormValidationResultFactory;
use Civi\Funding\Mock\Form\ApplicationFormMock;
use Civi\Funding\Mock\RemoteTools\JsonSchema\Validation\ValidationResultMock;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\ApplicationProcess\Handler\ApplicationFormNewValidateHandler
 * @covers \Civi\Funding\ApplicationProcess\Command\ApplicationFormNewValidateCommand
 */
final class ApplicationFormNewValidateHandlerTest extends TestCase {

  /**
   * @var \Civi\Funding\ApplicationProcess\Handler\ApplicationFormNewCreateHandlerInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $formCreateHandlerMock;

  private ApplicationFormNewValidateHandler $handler;

  /**
   * @var \Civi\Funding\ApplicationProcess\Form\Validation\ApplicationFormNewValidatorInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $formValidatorMock;

  /**
   * @var \Civi\Funding\ApplicationProcess\JsonSchema\Validator\ApplicationSchemaValidatorInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $jsonSchemaValidatorMock;

  protected function setUp(): void {
    parent::setUp();
    $this->formCreateHandlerMock = $this->createMock(ApplicationFormNewCreateHandlerInterface::class);
    $this->formValidatorMock = $this->createMock(ApplicationFormNewValidatorInterface::class);
    $this->jsonSchemaValidatorMock = $this->createMock(ApplicationSchemaValidatorInterface::class);
    $this->handler = new ApplicationFormNewValidateHandler(
      $this->formCreateHandlerMock,
      $this->formValidatorMock,
      $this->jsonSchemaValidatorMock
    );
  }

  public function testHandleValid(): void {
    $fundingProgram = FundingProgramFactory::createFundingProgram();
    $fundingCaseType = FundingCaseTypeFactory::createFundingCaseType();

    $data = ['_action' => 'save'];
    $validationResult = ApplicationFormValidationResultFactory::createValid();

    $form = new ApplicationFormMock();
    $this->formCreateHandlerMock->method('handle')
      ->with(new ApplicationFormNewCreateCommand($fundingCaseType, $fundingProgram))
      ->willReturn($form);

    $schemaValidationResult = new ApplicationSchemaValidationResult(new ValidationResultMock($data), [], []);
    $this->jsonSchemaValidatorMock->method('validate')
      ->with($form->getJsonSchema(), $data, 20)
      ->willReturn($schemaValidationResult);

    $this->formValidatorMock->expects(static::once())->method('validateInitial')
      ->with($fundingCaseType, $fundingProgram, $schemaValidationResult, FALSE)
      ->willReturn($validationResult);

    $command = new ApplicationFormNewValidateCommand($fundingProgram, $fundingCaseType, $data);
    $result = $this->handler->handle($command);
    static::assertSame($validationResult, $result);
  }

  public function testHandleInvalid(): void {
    $fundingProgram = FundingProgramFactory::createFundingProgram();
    $fundingCaseType = FundingCaseTypeFactory::createFundingCaseType();

    $data = ['_action' => 'save'];
    $errorMessages = ['/a/b' => ['error']];

    $form = new ApplicationFormMock();
    $this->formCreateHandlerMock->method('handle')
      ->with(new ApplicationFormNewCreateCommand($fundingCaseType, $fundingProgram))
      ->willReturn($form);

    $schemaValidationResult = new ValidationResultMock($data, $errorMessages);
    $this->jsonSchemaValidatorMock->method('validate')
      ->with($form->getJsonSchema(), $data, 20)
      ->willReturn(new ApplicationSchemaValidationResult($schemaValidationResult, [], []));

    $this->formValidatorMock->expects(static::never())->method('validateInitial');

    $command = new ApplicationFormNewValidateCommand($fundingProgram, $fundingCaseType, $data);
    $result = $this->handler->handle($command);
    static::assertInstanceOf(ValidatedApplicationDataInvalid::class, $result->getValidatedData());
    static::assertSame($data, $result->getData());
    static::assertSame($errorMessages, $result->getErrorMessages());
  }

}
