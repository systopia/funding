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

namespace Civi\RemoteTools\EventSubscriber;

use Civi\API\Event\AuthorizeEvent;
use Civi\API\Events;
use Civi\Core\CiviEventDispatcher;
use Civi\RemoteTools\Api4\Action\EventGetAction;
use Civi\RemoteTools\Event\AuthorizeApiRequestEvent;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\RemoteTools\EventSubscriber\ApiAuthorizeSubscriber
 */
final class ApiAuthorizeSubscriberTest extends TestCase {

  private ApiAuthorizeSubscriber $subscriber;

  /**
   * @var \PHPUnit\Framework\MockObject\MockObject&AuthorizeEvent
   */
  private MockObject $eventMock;

  /**
   * @var \PHPUnit\Framework\MockObject\MockObject&EventGetAction
   */
  private MockObject $requestMock;

  /**
   * @var \PHPUnit\Framework\MockObject\MockObject&CiviEventDispatcher
   */
  private MockObject $eventDispatcherMock;

  protected function setUp(): void {
    parent::setUp();

    $this->eventMock = $this->createMock(AuthorizeEvent::class);
    $this->requestMock = $this->createMock(EventGetAction::class);
    $this->requestMock->method('getAuthorizeRequestEventClass')->willReturn(AuthorizeApiRequestEvent::class);
    $this->requestMock->method('getAuthorizeRequestEventName')->willReturn('test.request.authorize');
    $this->eventMock->method('getApiRequest')->willReturn($this->requestMock);
    $this->eventDispatcherMock = $this->createMock(CiviEventDispatcher::class);
    $this->eventDispatcherMock = $this->createMock(CiviEventDispatcher::class);
    $this->subscriber = new ApiAuthorizeSubscriber();
  }

  public function testGetSubscribedEvents(): void {
    $expectedSubscriptions = [
      'civi.api.authorize' => ['onApiAuthorize', Events::W_EARLY],
    ];

    static::assertEquals($expectedSubscriptions, $this->subscriber::getSubscribedEvents());

    foreach ($expectedSubscriptions as [$method, $priority]) {
      static::assertTrue(method_exists(get_class($this->subscriber), $method));
    }
  }

  /**
   * @dataProvider provideAuthorize
   */
  public function testOnApiAuthorize(bool $authorize): void {
    $this->eventDispatcherMock->expects(static::once())->method('dispatch')
      ->with('test.request.authorize', static::isInstanceOf(AuthorizeApiRequestEvent::class))
      ->willReturnCallback(function (string $eventName, AuthorizeApiRequestEvent $event) use ($authorize) {
        static::assertSame($this->requestMock, $event->getApiRequest());
        $event->setAuthorized($authorize);
      });

    $this->eventMock->expects(static::once())->method('setAuthorized')->with($authorize);
    $this->eventMock->expects(static::once())->method('stopPropagation');
    $this->subscriber->onApiAuthorize($this->eventMock, 'civi.api.authorize', $this->eventDispatcherMock);
  }

  public function testOnApiAuthorizeNoListener(): void {
    $this->eventDispatcherMock->expects(static::once())->method('dispatch')
      ->with('test.request.authorize', static::isInstanceOf(AuthorizeApiRequestEvent::class));

    $this->eventMock->expects(static::never())->method('setAuthorized');
    $this->eventMock->expects(static::never())->method('stopPropagation');
    $this->subscriber->onApiAuthorize($this->eventMock, 'civi.api.authorize', $this->eventDispatcherMock);
  }

  /**
   * @phpstan-return iterable<array{bool}>
   */
  public function provideAuthorize(): iterable {
    yield [TRUE];
    yield [FALSE];
  }

}
