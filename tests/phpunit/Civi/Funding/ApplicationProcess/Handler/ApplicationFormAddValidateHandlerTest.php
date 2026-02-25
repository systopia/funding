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

use Civi\Funding\ApplicationProcess\Command\ApplicationFormAddCreateCommand;
use Civi\Funding\ApplicationProcess\Command\ApplicationFormAddValidateCommand;
use Civi\Funding\ApplicationProcess\Form\Validation\ApplicationFormAddValidatorInterface;
use Civi\Funding\ApplicationProcess\JsonSchema\Validator\ApplicationSchemaValidationResult;
use Civi\Funding\ApplicationProcess\JsonSchema\Validator\ApplicationSchemaValidatorInterface;
use Civi\Funding\EntityFactory\FundingCaseBundleFactory;
use Civi\Funding\Form\Application\ValidatedApplicationDataInvalid;
use Civi\Funding\Mock\ApplicationProcess\Form\Validation\ApplicationFormValidationResultFactory;
use Civi\Funding\Mock\Form\ApplicationFormMock;
use Civi\Funding\Mock\RemoteTools\JsonSchema\Validation\ValidationResultMock;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\ApplicationProcess\Handler\ApplicationFormAddValidateHandler
 * @covers \Civi\Funding\ApplicationProcess\Command\ApplicationFormAddValidateCommand
 */
final class ApplicationFormAddValidateHandlerTest extends TestCase {

  /**
   * @var \Civi\Funding\ApplicationProcess\Handler\ApplicationFormAddCreateHandlerInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $formCreateHandlerMock;

  private ApplicationFormAddValidateHandler $handler;

  /**
   * @var \Civi\Funding\ApplicationProcess\Form\Validation\ApplicationFormAddValidatorInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $formValidatorMock;

  /**
   * @var \Civi\Funding\ApplicationProcess\JsonSchema\Validator\ApplicationSchemaValidatorInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $jsonSchemaValidatorMock;

  protected function setUp(): void {
    parent::setUp();
    $this->formCreateHandlerMock = $this->createMock(ApplicationFormAddCreateHandlerInterface::class);
    $this->formValidatorMock = $this->createMock(ApplicationFormAddValidatorInterface::class);
    $this->jsonSchemaValidatorMock = $this->createMock(ApplicationSchemaValidatorInterface::class);
    $this->handler = new ApplicationFormAddValidateHandler(
      $this->formCreateHandlerMock,
      $this->formValidatorMock,
      $this->jsonSchemaValidatorMock
    );
  }

  public function testHandleValid(): void {
    $contactId = 1;
    $fundingCaseBundle = FundingCaseBundleFactory::create();
    $fundingProgram = $fundingCaseBundle->getFundingProgram();
    $fundingCaseType = $fundingCaseBundle->getFundingCaseType();
    $fundingCase = $fundingCaseBundle->getFundingCase();

    $data = ['_action' => 'save'];
    $validationResult = ApplicationFormValidationResultFactory::createValid();

    $form = new ApplicationFormMock();
    $this->formCreateHandlerMock->method('handle')
      ->with(new ApplicationFormAddCreateCommand($contactId, $fundingCaseBundle))
      ->willReturn($form);

    $schemaValidationResult = new ApplicationSchemaValidationResult(new ValidationResultMock($data), [], []);
    $this->jsonSchemaValidatorMock->method('validate')
      ->with($form->getJsonSchema(), $data, 20)
      ->willReturn($schemaValidationResult);

    $this->formValidatorMock->expects(static::once())->method('validateAdd')
      ->with($fundingCase, $fundingCaseType, $fundingProgram, $schemaValidationResult, FALSE)
      ->willReturn($validationResult);

    $command = new ApplicationFormAddValidateCommand($contactId, $fundingCaseBundle, $data);
    $result = $this->handler->handle($command);
    static::assertSame($validationResult, $result);
  }

  public function testHandleInvalid(): void {
    $contactId = 1;
    $fundingCaseBundle = FundingCaseBundleFactory::create();

    $data = ['_action' => 'save'];
    $errorMessages = ['/a/b' => ['error']];

    $form = new ApplicationFormMock();
    $this->formCreateHandlerMock->method('handle')
      ->with(new ApplicationFormAddCreateCommand($contactId, $fundingCaseBundle))
      ->willReturn($form);

    $schemaValidationResult = new ValidationResultMock($data, $errorMessages);
    $this->jsonSchemaValidatorMock->method('validate')
      ->with($form->getJsonSchema(), $data, 20)
      ->willReturn(new ApplicationSchemaValidationResult($schemaValidationResult, [], []));

    $this->formValidatorMock->expects(static::never())->method('validateAdd');

    $command = new ApplicationFormAddValidateCommand($contactId, $fundingCaseBundle, $data);
    $result = $this->handler->handle($command);
    static::assertInstanceOf(ValidatedApplicationDataInvalid::class, $result->getValidatedData());
    static::assertSame($data, $result->getData());
    static::assertSame($errorMessages, $result->getErrorMessages());
  }

}
