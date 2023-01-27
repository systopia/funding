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

/**
 * @noinspection PhpUnhandledExceptionInspection
 * @noinspection PropertyAnnotationInspection
 */

declare(strict_types = 1);

namespace Civi\RemoteTools\Api4\Action;

use Civi\Api4\Generic\Result;
use Civi\Core\CiviEventDispatcherInterface;
use Civi\RemoteTools\Event\GetEvent;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub\ReturnCallback;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\RemoteTools\Api4\Action\EventGetAction
 * @covers \Civi\RemoteTools\Event\GetEvent
 * @covers \Civi\RemoteTools\Event\AbstractRequestEvent
 */
final class EventGetActionTest extends TestCase {

  /**
   * @var \PHPUnit\Framework\MockObject\MockObject&\Civi\Core\CiviEventDispatcherInterface
   */
  private MockObject $eventDispatcherMock;

  private EventGetAction $eventGetAction;

  protected function setUp(): void {
    parent::setUp();
    $this->eventDispatcherMock = $this->createMock(CiviEventDispatcherInterface::class);
    $this->eventGetAction = new EventGetAction(
      'test.request.init',
      'test.request.authorize',
      'test',
      'action',
      $this->eventDispatcherMock
    );
  }

  public function testRun(): void {
    $result = new Result();

    $this->eventDispatcherMock->expects(static::exactly(3))->method('dispatch')
      ->withConsecutive(
        [GetEvent::getEventName('test', 'action'), static::isInstanceOf(GetEvent::class)],
        [GetEvent::getEventName('test'), static::isInstanceOf(GetEvent::class)],
        [GetEvent::getEventName(), static::isInstanceOf(GetEvent::class)]
      )
      ->willReturnOnConsecutiveCalls(
        new ReturnCallback(function (string $eventName, GetEvent $event) {
          $event->setRowCount(123);
          $event->addRecord(['foo' => 'bar']);
        })
      );

    $this->eventGetAction->_run($result);

    static::assertSame(123, $result->rowCount);
    static::assertSame([['foo' => 'bar']], $result->getArrayCopy());
  }

}
