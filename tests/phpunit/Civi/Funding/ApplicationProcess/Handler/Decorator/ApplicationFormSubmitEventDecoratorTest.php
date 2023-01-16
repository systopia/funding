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

use Civi\Core\CiviEventDispatcher;
use Civi\Funding\ApplicationProcess\Command\ApplicationFormNewSubmitResult;
use Civi\Funding\ApplicationProcess\Command\ApplicationFormSubmitCommand;
use Civi\Funding\ApplicationProcess\Command\ApplicationFormSubmitResult;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormSubmitHandlerInterface;
use Civi\Funding\EntityFactory\ApplicationProcessBundleFactory;
use Civi\Funding\Event\ApplicationProcess\ApplicationFormSubmitSuccessEvent;
use Civi\Funding\Form\Validation\ValidationResult;
use Civi\Funding\Mock\Form\FundingCaseType\TestValidatedData;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Systopia\JsonSchema\Errors\ErrorCollector;

/**
 * @covers \Civi\Funding\ApplicationProcess\Handler\Decorator\ApplicationFormSubmitEventDecorator
 */
final class ApplicationFormSubmitEventDecoratorTest extends TestCase {

  /**
   * @var \Civi\Funding\ApplicationProcess\Handler\ApplicationFormSubmitHandlerInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $decoratedHandlerMock;

  /**
   * @var \Civi\Core\CiviEventDispatcher&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $eventDispatcherMock;

  private ApplicationFormSubmitEventDecorator $handler;

  protected function setUp(): void {
    parent::setUp();
    $this->decoratedHandlerMock = $this->createMock(ApplicationFormSubmitHandlerInterface::class);
    $this->eventDispatcherMock = $this->createMock(CiviEventDispatcher::class);
    $this->handler = new ApplicationFormSubmitEventDecorator(
      $this->decoratedHandlerMock,
      $this->eventDispatcherMock,
    );
  }

  public function testHandle(): void {
    $command = $this->createCommand();
    $result = ApplicationFormSubmitResult::createSuccess(
      new ValidationResult([], new ErrorCollector()),
      new TestValidatedData([]),
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
    $result = ApplicationFormSubmitResult::createError(new ValidationResult([], new ErrorCollector()));

    $this->decoratedHandlerMock->expects(static::once())->method('handle')
      ->with($command)
      ->willReturn($result);

    $this->eventDispatcherMock->expects(static::never())->method('dispatch');

    static::assertSame($result, $this->handler->handle($command));
  }

  private function createCommand(): ApplicationFormSubmitCommand {
    return new ApplicationFormSubmitCommand(
      1,
      ApplicationProcessBundleFactory::createApplicationProcessBundle(),
      ['test' => 'foo'],
    );
  }

}
