<?php
/*
 * Copyright (C) 2023 SYSTOPIA GmbH
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

namespace Civi\Funding\ApplicationProcess\Handler\Decorator;

use Civi\Core\CiviEventDispatcherInterface;
use Civi\Funding\ApplicationProcess\Command\ApplicationFormNewSubmitCommand;
use Civi\Funding\ApplicationProcess\Command\ApplicationFormNewSubmitResult;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormNewSubmitHandlerInterface;
use Civi\Funding\Entity\ApplicationProcessEntityBundle;
use Civi\Funding\EntityFactory\ApplicationProcessFactory;
use Civi\Funding\EntityFactory\FundingCaseFactory;
use Civi\Funding\EntityFactory\FundingCaseTypeFactory;
use Civi\Funding\EntityFactory\FundingProgramFactory;
use Civi\Funding\Event\ApplicationProcess\ApplicationFormSubmitSuccessEvent;
use Civi\Funding\Form\Validation\ValidationResult;
use Civi\Funding\Mock\Form\FundingCaseType\TestValidatedData;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Systopia\JsonSchema\Errors\ErrorCollector;

/**
 * @covers \Civi\Funding\ApplicationProcess\Handler\Decorator\ApplicationFormNewSubmitEventDecorator
 */
final class ApplicationFormNewSubmitEventDecoratorTest extends TestCase {

  /**
   * @var \Civi\Funding\ApplicationProcess\Handler\ApplicationFormNewSubmitHandlerInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $decoratedHandlerMock;

  /**
   * @var \Civi\Core\CiviEventDispatcherInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $eventDispatcherMock;

  private ApplicationFormNewSubmitEventDecorator $handler;

  protected function setUp(): void {
    parent::setUp();
    $this->decoratedHandlerMock = $this->createMock(ApplicationFormNewSubmitHandlerInterface::class);
    $this->eventDispatcherMock = $this->createMock(CiviEventDispatcherInterface::class);
    $this->handler = new ApplicationFormNewSubmitEventDecorator(
      $this->decoratedHandlerMock,
      $this->eventDispatcherMock,
    );
  }

  public function testHandle(): void {
    $command = $this->createCommand();
    $result = ApplicationFormNewSubmitResult::createSuccess(
      new ValidationResult([], new ErrorCollector()),
      new TestValidatedData([]),
      new ApplicationProcessEntityBundle(
        ApplicationProcessFactory::createApplicationProcess(),
        FundingCaseFactory::createFundingCase(),
        $command->getFundingCaseType(),
        $command->getFundingProgram(),
      ),
    );

    $this->decoratedHandlerMock->expects(static::once())->method('handle')
      ->with($command)
      ->willReturn($result);

    $this->eventDispatcherMock->expects(static::once())->method('dispatch')
      ->with(ApplicationFormSubmitSuccessEvent::class, static::isInstanceOf(ApplicationFormSubmitSuccessEvent::class));

    static::assertSame($result, $this->handler->handle($command));
  }

  public function testHandleInvalid(): void {
    $command = $this->createCommand();
    $result = ApplicationFormNewSubmitResult::createError(new ValidationResult([], new ErrorCollector()));

    $this->decoratedHandlerMock->expects(static::once())->method('handle')
      ->with($command)
      ->willReturn($result);

    $this->eventDispatcherMock->expects(static::never())->method('dispatch');

    static::assertSame($result, $this->handler->handle($command));
  }

  private function createCommand(): ApplicationFormNewSubmitCommand {
    return new ApplicationFormNewSubmitCommand(
      1,
      FundingCaseTypeFactory::createFundingCaseType(),
      FundingProgramFactory::createFundingProgram(),
      ['test' => 'foo'],
    );
  }

}
