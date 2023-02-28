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
 * @noinspection PropertyAnnotationInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

declare(strict_types = 1);

namespace Civi\RemoteTools\EventSubscriber;

use Civi\API\Event\AuthorizeEvent;
use Civi\Core\CiviEventDispatcherInterface;
use Civi\RemoteTools\Api4\Action\EventGetAction;
use Civi\RemoteTools\Event\AuthorizeApiRequestEvent;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\RemoteTools\EventSubscriber\ApiAuthorizeInitRequestSubscriber
 * @covers \Civi\RemoteTools\Event\AuthorizeApiRequestEvent
 */
final class ApiAuthorizeInitRequestSubscriberTest extends TestCase {

  private ApiAuthorizeInitRequestSubscriber $subscriber;

  /**
   * @var \PHPUnit\Framework\MockObject\MockObject&AuthorizeEvent
   */
  private MockObject $eventMock;

  /**
   * @var \PHPUnit\Framework\MockObject\MockObject&EventGetAction
   */
  private MockObject $requestMock;

  /**
   * @var \PHPUnit\Framework\MockObject\MockObject&CiviEventDispatcherInterface
   */
  private MockObject $eventDispatcherMock;

  protected function setUp(): void {
    parent::setUp();

    $this->eventMock = $this->createMock(AuthorizeEvent::class);
    $this->requestMock = $this->createMock(EventGetAction::class);
    $this->requestMock->method('getInitRequestEventClass')->willReturn(AuthorizeApiRequestEvent::class);
    $this->requestMock->method('getInitRequestEventName')->willReturn('test.request.init');
    $this->eventMock->method('getApiRequest')->willReturn($this->requestMock);
    $this->eventDispatcherMock = $this->createMock(CiviEventDispatcherInterface::class);

    $this->subscriber = new ApiAuthorizeInitRequestSubscriber($this->eventDispatcherMock);
  }

  public function testGetSubscribedEvents(): void {
    $expectedSubscriptions = [
      'civi.api.authorize' => ['onApiAuthorize', PHP_INT_MAX],
    ];

    static::assertEquals($expectedSubscriptions, ApiAuthorizeInitRequestSubscriber::getSubscribedEvents());

    foreach ($expectedSubscriptions as $event => $method) {
      static::assertTrue(method_exists(ApiAuthorizeInitRequestSubscriber::class, $method[0]));
    }
  }

  public function testOnApiAuthorize(): void {
    $this->requestMock->expects(static::once())->method('getRequiredExtraParams')->willReturn(['foo']);

    $this->eventDispatcherMock->expects(static::once())->method('dispatch')
      ->with('test.request.init', static::isInstanceOf(AuthorizeApiRequestEvent::class))
      ->willReturnCallback(function (string $eventName, AuthorizeApiRequestEvent $event) {
        static::assertSame($this->requestMock, $event->getApiRequest());
        $this->requestMock->expects(static::once())->method('hasExtraParam')->with('foo')->willReturn(TRUE);
      });

    $this->subscriber->onApiAuthorize($this->eventMock);
  }

  public function testOnApiAuthorizeRequiredExtraParamMissing(): void {
    $this->requestMock->expects(static::once())->method('getRequiredExtraParams')->willReturn(['foo']);

    $this->eventDispatcherMock->expects(static::once())->method('dispatch')
      ->with('test.request.init', static::isInstanceOf(AuthorizeApiRequestEvent::class))
      ->willReturnCallback(function (string $eventName, AuthorizeApiRequestEvent $event) {
        static::assertSame($this->requestMock, $event->getApiRequest());
      });

    $this->requestMock->expects(static::once())->method('hasExtraParam')->with('foo')->willReturn(FALSE);

    static::expectException(\CRM_Core_Exception::class);
    static::expectExceptionMessage('Required extra param "foo" is missing');
    $this->subscriber->onApiAuthorize($this->eventMock);
  }

}
