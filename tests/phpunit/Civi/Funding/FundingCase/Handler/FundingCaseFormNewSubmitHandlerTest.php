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

namespace Civi\Funding\FundingCase\Handler;

use Civi\Funding\EntityFactory\FundingCaseFactory;
use Civi\Funding\EntityFactory\FundingCaseTypeFactory;
use Civi\Funding\EntityFactory\FundingProgramFactory;
use Civi\Funding\Form\FundingCase\FundingCaseValidationResult;
use Civi\Funding\Form\FundingCase\ValidatedFundingCaseDataInvalid;
use Civi\Funding\FundingCase\Command\FundingCaseFormNewSubmitCommand;
use Civi\Funding\FundingCase\Command\FundingCaseFormNewValidateCommand;
use Civi\Funding\FundingCase\FundingCaseManager;
use Civi\Funding\Mock\FundingCaseType\FundingCase\Validation\ValidatedFundingCaseDataMock;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\FundingCase\Handler\FundingCaseFormNewSubmitHandler
 * @covers \Civi\Funding\FundingCase\Command\FundingCaseFormNewSubmitCommand
 * @covers \Civi\Funding\FundingCase\Command\FundingCaseFormNewSubmitResult
 */
final class FundingCaseFormNewSubmitHandlerTest extends TestCase {

  /**
   * @var \Civi\Funding\FundingCase\FundingCaseManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $fundingCaseManagerMock;

  private FundingCaseFormNewSubmitHandler $handler;

  /**
   * @var \Civi\Funding\FundingCase\Handler\FundingCaseFormNewValidateHandlerInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $validateHandlerMock;

  protected function setUp(): void {
    parent::setUp();
    $this->fundingCaseManagerMock = $this->createMock(FundingCaseManager::class);
    $this->validateHandlerMock = $this->createMock(FundingCaseFormNewValidateHandlerInterface::class);
    $this->handler = new FundingCaseFormNewSubmitHandler(
      $this->fundingCaseManagerMock,
      $this->validateHandlerMock,
    );
  }

  public function testHandle(): void {
    $command = $this->createCommand();

    $recipientContactId = 123;
    $validatedData = new ValidatedFundingCaseDataMock([
      '_action' => 'save',
      'title' => 'Test',
      'recipient' => $recipientContactId,
    ]);
    $validationResult = FundingCaseValidationResult::newValid($validatedData);
    $this->validateHandlerMock->method('handle')->with(new FundingCaseFormNewValidateCommand(
      $command->getContactId(),
      $command->getFundingProgram(),
      $command->getFundingCaseType(),
      $command->getData()
    ))->willReturn($validationResult);

    $fundingCase = FundingCaseFactory::createFundingCase();
    $this->fundingCaseManagerMock->expects(static::once())->method('create')
      ->with(
        $command->getContactId(),
        [
          'funding_program' => $command->getFundingProgram(),
          'funding_case_type' => $command->getFundingCaseType(),
          'recipient_contact_id' => $recipientContactId,
        ]
      )->willReturn($fundingCase);

    $result = $this->handler->handle($command);

    static::assertTrue($result->isSuccess());
    static::assertSame($validationResult, $result->getValidationResult());
    static::assertSame($validatedData, $result->getValidatedData());
    static::assertSame($fundingCase, $result->getFundingCase());
  }

  public function testHandleInvalid(): void {
    $command = $this->createCommand();

    $validatedData = new ValidatedFundingCaseDataInvalid([]);
    $errorMessages = ['/field' => ['error']];
    $validationResult = FundingCaseValidationResult::newInvalid($errorMessages, $validatedData);
    $this->validateHandlerMock->method('handle')->with(new FundingCaseFormNewValidateCommand(
      $command->getContactId(),
      $command->getFundingProgram(),
      $command->getFundingCaseType(),
      $command->getData()
    ))->willReturn($validationResult);

    $this->fundingCaseManagerMock->expects(static::never())->method('update');

    $result = $this->handler->handle($command);

    static::assertFalse($result->isSuccess());
    static::assertSame($validationResult, $result->getValidationResult());
    static::assertSame($validatedData, $result->getValidatedData());
    static::assertNull($result->getFundingCase());
  }

  private function createCommand(): FundingCaseFormNewSubmitCommand {
    return new FundingCaseFormNewSubmitCommand(
      1,
      FundingProgramFactory::createFundingProgram(),
      FundingCaseTypeFactory::createFundingCaseType(),
      ['test' => 'foo'],
    );
  }

}
