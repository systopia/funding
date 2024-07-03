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
use Civi\Funding\FundingCase\FundingCaseManager;
use Civi\Funding\FundingCase\Handler\Helper\ApplicationAllowedActionApplier;
use Civi\Funding\FundingCase\StatusDeterminer\FundingCaseStatusDeterminerInterface;
use Civi\Funding\Mock\FundingCaseType\FundingCase\Validation\ValidatedFundingCaseDataMock;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\FundingCase\Handler\FundingCaseFormUpdateSubmitHandler
 * @covers \Civi\Funding\FundingCase\Command\FundingCaseFormUpdateSubmitCommand
 * @covers \Civi\Funding\FundingCase\Command\FundingCaseFormUpdateSubmitResult
 */
final class FundingCaseFormUpdateSubmitHandlerTest extends TestCase {

  /**
   * @var \Civi\Funding\FundingCase\Handler\Helper\ApplicationAllowedActionApplier&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $applicationAllowedActionApplierMock;

  /**
   * @var \Civi\Funding\FundingCase\FundingCaseManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $fundingCaseManagerMock;

  private FundingCaseFormUpdateSubmitHandler $handler;

  /**
   * @var \Civi\Funding\FundingCase\StatusDeterminer\FundingCaseStatusDeterminerInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $statusDeterminerMock;

  /**
   * @var \Civi\Funding\FundingCase\Handler\FundingCaseFormUpdateValidateHandlerInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $validateHandlerMock;

  protected function setUp(): void {
    parent::setUp();
    $this->applicationAllowedActionApplierMock = $this->createMock(ApplicationAllowedActionApplier::class);
    $this->fundingCaseManagerMock = $this->createMock(FundingCaseManager::class);
    $this->statusDeterminerMock = $this->createMock(FundingCaseStatusDeterminerInterface::class);
    $this->validateHandlerMock = $this->createMock(FundingCaseFormUpdateValidateHandlerInterface::class);
    $this->handler = new FundingCaseFormUpdateSubmitHandler(
      $this->applicationAllowedActionApplierMock,
      $this->fundingCaseManagerMock,
      $this->statusDeterminerMock,
      $this->validateHandlerMock,
    );
  }

  public function testHandle(): void {
    $command = $this->createCommand();

    $recipientContactId = 123;
    $validatedData = new ValidatedFundingCaseDataMock([
      '_action' => 'apply',
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

    $this->applicationAllowedActionApplierMock->expects(static::once())->method('applyAllowedActionsByFundingCase')
      ->with($command->getContactId(), $command->getFundingCase(), 'apply');

    $this->statusDeterminerMock->method('getStatus')
      ->with($command->getFundingCase()->getStatus(), 'apply')
      ->willReturn('new_status');
    $this->fundingCaseManagerMock->expects(static::once())->method('update')
      ->with($command->getFundingCase());

    $result = $this->handler->handle($command);

    static::assertTrue($result->isSuccess());
    static::assertSame($validationResult, $result->getValidationResult());
    static::assertSame($validatedData, $result->getValidatedData());
    static::assertSame($command->getFundingCase(), $result->getFundingCase());
    static::assertSame('new_status', $command->getFundingCase()->getStatus());
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

    $this->applicationAllowedActionApplierMock->expects(static::never())->method('applyAllowedActionsByFundingCase');
    $this->statusDeterminerMock->expects(static::never())->method('getStatus');
    $this->fundingCaseManagerMock->expects(static::never())->method('update');

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
