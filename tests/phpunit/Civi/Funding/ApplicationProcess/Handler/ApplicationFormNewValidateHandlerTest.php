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

use Civi\Funding\ApplicationProcess\Command\ApplicationFormNewValidateCommand;
use Civi\Funding\EntityFactory\FundingCaseTypeFactory;
use Civi\Funding\EntityFactory\FundingProgramFactory;
use Civi\Funding\Form\ApplicationValidationResult;
use Civi\Funding\Form\NonSummaryApplicationValidatorInterface;
use Civi\Funding\Mock\Form\ValidatedApplicationDataMock;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\ApplicationProcess\Handler\ApplicationFormNewValidateHandler
 * @covers \Civi\Funding\ApplicationProcess\Command\ApplicationFormNewValidateCommand
 */
final class ApplicationFormNewValidateHandlerTest extends TestCase {

  private ApplicationFormNewValidateHandler $handler;

  /**
   * @var \Civi\Funding\Form\NonSummaryApplicationValidatorInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $validatorMock;

  protected function setUp(): void {
    parent::setUp();
    $this->validatorMock = $this->createMock(NonSummaryApplicationValidatorInterface::class);
    $this->handler = new ApplicationFormNewValidateHandler($this->validatorMock);
  }

  public function testHandle(): void {
    $contactId = 1;
    $fundingProgram = FundingProgramFactory::createFundingProgram();
    $fundingCaseType = FundingCaseTypeFactory::createFundingCaseType();

    $data = ['foo' => 'bar'];
    $validatedData = new ValidatedApplicationDataMock();
    $errorMessages = ['/a/b' => ['error']];
    $validationResult = ApplicationValidationResult::newInvalid($errorMessages, $validatedData);

    $this->validatorMock->expects(static::once())->method('validateInitial')
      ->with($contactId, $fundingProgram, $fundingCaseType, $data, 20)
      ->willReturn($validationResult);

    $command = new ApplicationFormNewValidateCommand($contactId, $fundingProgram, $fundingCaseType, $data);
    $result = $this->handler->handle($command);
    static::assertSame($validationResult, $result);
  }

}
