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
use Civi\Funding\Form\FundingCase\FundingCaseValidatorInterface;
use Civi\Funding\FundingCase\Command\FundingCaseFormUpdateValidateCommand;
use Civi\Funding\Mock\Form\FundingCaseType\FundingCase\TestFundingCaseValidatedData;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\FundingCase\Handler\FundingCaseFormUpdateValidateHandler
 * @covers \Civi\Funding\FundingCase\Command\FundingCaseFormUpdateValidateCommand
 * @covers \Civi\Funding\Form\FundingCase\FundingCaseValidationResult
 */
final class FundingCaseFormUpdateValidateHandlerTest extends TestCase {

  private FundingCaseFormUpdateValidateHandler $handler;

  /**
   * @var \Civi\Funding\Form\FundingCase\FundingCaseValidatorInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $validatorMock;

  protected function setUp(): void {
    parent::setUp();
    $this->validatorMock = $this->createMock(FundingCaseValidatorInterface::class);
    $this->handler = new FundingCaseFormUpdateValidateHandler($this->validatorMock);
  }

  public function testHandle(): void {
    $contactId = 1;
    $fundingProgram = FundingProgramFactory::createFundingProgram();
    $fundingCaseType = FundingCaseTypeFactory::createFundingCaseType();
    $fundingCase = FundingCaseFactory::createFundingCase();

    $data = ['foo' => 'bar'];
    $validatedData = new TestFundingCaseValidatedData([]);
    $errorMessages = ['/a/b' => ['error']];
    $validationResult = FundingCaseValidationResult::newInvalid($errorMessages, $validatedData);

    $this->validatorMock->expects(static::once())->method('validateUpdate')
      ->with($fundingProgram, $fundingCaseType, $fundingCase, $data, 20)
      ->willReturn($validationResult);

    $command = new FundingCaseFormUpdateValidateCommand(
      $fundingProgram,
      $fundingCaseType,
      $fundingCase,
      $data,
    );
    $result = $this->handler->handle($command);
    static::assertSame($validatedData, $result->getValidatedData());
    static::assertSame($errorMessages, $result->getErrorMessages());
    static::assertFalse($result->isValid());
  }

}
