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
use Civi\Funding\FundingCase\Command\FundingCaseFormUpdateSubmitCommand;
use Civi\Funding\FundingCase\Command\FundingCaseFormUpdateValidateCommand;
use Civi\Funding\Mock\Form\FundingCaseType\FundingCase\TestFundingCaseValidatedData;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\FundingCase\Handler\FundingCaseFormUpdateSubmitHandler
 * @covers \Civi\Funding\FundingCase\Command\FundingCaseFormUpdateSubmitCommand
 * @covers \Civi\Funding\FundingCase\Command\FundingCaseFormUpdateSubmitResult
 */
final class FundingCaseFormUpdateSubmitHandlerTest extends TestCase {

  private FundingCaseFormUpdateSubmitHandler $handler;

  /**
   * @var \Civi\Funding\FundingCase\Handler\FundingCaseFormUpdateValidateHandlerInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $validateHandlerMock;

  protected function setUp(): void {
    parent::setUp();
    $this->validateHandlerMock = $this->createMock(FundingCaseFormUpdateValidateHandlerInterface::class);
    $this->handler = new FundingCaseFormUpdateSubmitHandler(
      $this->validateHandlerMock,
    );
  }

  public function testHandle(): void {
    $command = $this->createCommand();

    $recipientContactId = 123;
    $validatedData = new TestFundingCaseValidatedData([
      'action' => 'save',
      'title' => 'Test',
      'recipient' => $recipientContactId,
    ]);
    $validationResult = FundingCaseValidationResult::newValid($validatedData);
    $this->validateHandlerMock->method('handle')->with(new FundingCaseFormUpdateValidateCommand(
      $command->getFundingProgram(),
      $command->getFundingCaseType(),
      $command->getFundingCase(),
      $command->getData(),
    ))->willReturn($validationResult);

    $result = $this->handler->handle($command);

    static::assertTrue($result->isSuccess());
    static::assertSame($validationResult, $result->getValidationResult());
    static::assertSame($validatedData, $result->getValidatedData());
    static::assertSame($command->getFundingCase(), $result->getFundingCase());
  }

  public function testHandleInvalid(): void {
    $command = $this->createCommand();

    $validatedData = new ValidatedFundingCaseDataInvalid([]);
    $errorMessages = ['/field' => ['error']];
    $validationResult = FundingCaseValidationResult::newInvalid($errorMessages, $validatedData);
    $this->validateHandlerMock->method('handle')->with(new FundingCaseFormUpdateValidateCommand(
      $command->getFundingProgram(),
      $command->getFundingCaseType(),
      $command->getFundingCase(),
      $command->getData(),
    ))->willReturn($validationResult);

    $result = $this->handler->handle($command);

    static::assertFalse($result->isSuccess());
    static::assertSame($validationResult, $result->getValidationResult());
    static::assertSame($validatedData, $result->getValidatedData());
    static::assertSame($command->getFundingCase(), $result->getFundingCase());
  }

  private function createCommand(): FundingCaseFormUpdateSubmitCommand {
    return new FundingCaseFormUpdateSubmitCommand(
      1,
      FundingProgramFactory::createFundingProgram(),
      FundingCaseTypeFactory::createFundingCaseType(),
      FundingCaseFactory::createFundingCase(),
      ['test' => 'foo'],
    );
  }

}
