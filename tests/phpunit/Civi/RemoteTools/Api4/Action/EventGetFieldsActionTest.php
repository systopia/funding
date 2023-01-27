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

namespace Civi\RemoteTools\Api4\Action;

use Civi\Core\CiviEventDispatcherInterface;
use Civi\RemoteTools\Event\GetFieldsEvent;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub\ReturnCallback;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\RemoteTools\Api4\Action\EventGetFieldsAction
 * @covers \Civi\RemoteTools\Event\GetFieldsEvent
 * @covers \Civi\RemoteTools\Event\AbstractRequestEvent
 */
final class EventGetFieldsActionTest extends TestCase {

  /**
   * @var \PHPUnit\Framework\MockObject\MockObject&\Civi\Core\CiviEventDispatcherInterface
   */
  private MockObject $eventDispatcherMock;

  private EventGetFieldsAction $action;

  protected function setUp(): void {
    parent::setUp();
    $this->eventDispatcherMock = $this->createMock(CiviEventDispatcherInterface::class);
    $this->action = new EventGetFieldsAction(
      'test.request.init',
      'test.request.authorize',
      'test',
      'action',
      $this->eventDispatcherMock
    );
  }

  public function test(): void {
    static::assertSame([], $this->action->fields());

    $this->eventDispatcherMock->expects(static::exactly(3))->method('dispatch')
      ->withConsecutive(
        [GetFieldsEvent::getEventName('test', 'action'), static::isInstanceOf(GetFieldsEvent::class)],
        [GetFieldsEvent::getEventName('test'), static::isInstanceOf(GetFieldsEvent::class)],
        [GetFieldsEvent::getEventName(), static::isInstanceOf(GetFieldsEvent::class)]
      )
      ->willReturnOnConsecutiveCalls(
        new ReturnCallback(function (string $eventName, GetFieldsEvent $event) {
          $event->addField(['name' => 'foo', 'x' => 'y']);
        })
      );

    $records = $this->action->getRecords();
    static::assertSame(['foo' => ['name' => 'foo', 'x' => 'y']], $records);
  }

}
