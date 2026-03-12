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

use Civi\Funding\ApplicationProcess\Command\ApplicationActionApplyCommand;
use Civi\Funding\ApplicationProcess\Command\ApplicationFormSubmitCommand;
use Civi\Funding\ApplicationProcess\Command\ApplicationFormValidateCommand;
use Civi\Funding\Entity\FullApplicationProcessStatus;
use Civi\Funding\EntityFactory\ApplicationProcessBundleFactory;
use Civi\Funding\Mock\ApplicationProcess\Form\Validation\ApplicationFormValidationResultFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\ApplicationProcess\Handler\ApplicationFormSubmitHandler
 * @covers \Civi\Funding\ApplicationProcess\Command\ApplicationFormSubmitCommand
 * @covers \Civi\Funding\ApplicationProcess\Command\ApplicationFormSubmitResult
 * @covers \Civi\Funding\ApplicationProcess\Command\AbstractApplicationFormSubmitResult
 */
final class ApplicationFormSubmitHandlerTest extends TestCase {

  /**
   * @var \Civi\Funding\ApplicationProcess\Handler\ApplicationActionApplyHandlerInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $actionApplyHandlerMock;

  private ApplicationFormSubmitHandler $handler;

  /**
   * @var \Civi\Funding\ApplicationProcess\Handler\ApplicationFormValidateHandlerInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $validateHandlerMock;

  protected function setUp(): void {
    parent::setUp();
    $this->actionApplyHandlerMock = $this->createMock(ApplicationActionApplyHandlerInterface::class);
    $this->validateHandlerMock = $this->createMock(ApplicationFormValidateHandlerInterface::class);
    $this->handler = new ApplicationFormSubmitHandler(
      $this->actionApplyHandlerMock,
      $this->validateHandlerMock,
    );
  }

  public function testHandleValid(): void {
    $command = $this->createCommand();
    $validationResult = ApplicationFormValidationResultFactory::createValid();
    $this->validateHandlerMock->method('handle')->with(new ApplicationFormValidateCommand(
      $command->getApplicationProcessBundle(),
      $command->getApplicationProcessStatusList(),
      $command->getData()
    ))->willReturn($validationResult);

    $this->actionApplyHandlerMock->expects(static::once())->method('handle')->with(new ApplicationActionApplyCommand(
      $command->getContactId(),
      $validationResult->getAction(),
      $command->getApplicationProcessBundle(),
      $validationResult,
    ));

    $result = $this->handler->handle($command);

    static::assertTrue($result->isSuccess());
    static::assertSame($validationResult, $result->getValidationResult());
  }

  public function testHandleInvalid(): void {
    $command = $this->createCommand();
    $errorMessages = ['/field' => ['error']];
    $validationResult = ApplicationFormValidationResultFactory::createInvalid($errorMessages);

    $this->validateHandlerMock->method('handle')->with(new ApplicationFormValidateCommand(
      $command->getApplicationProcessBundle(),
      $command->getApplicationProcessStatusList(),
      $command->getData(),
    ))->willReturn($validationResult);

    $this->actionApplyHandlerMock->expects(static::never())->method('handle');

    $result = $this->handler->handle($command);

    static::assertFalse($result->isSuccess());
    static::assertSame($validationResult, $result->getValidationResult());
  }

  private function createCommand(): ApplicationFormSubmitCommand {
    return new ApplicationFormSubmitCommand(
      1,
      ApplicationProcessBundleFactory::createApplicationProcessBundle(),
      [23 => new FullApplicationProcessStatus('status', NULL, NULL)],
      ['test' => 'foo'],
    );
  }

}
