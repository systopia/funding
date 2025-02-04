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

namespace Civi\Funding\EventSubscriber\Remote;

use Civi\Funding\Contact\FundingRemoteContactIdResolverInterface;
use Civi\Funding\Event\Remote\RemotePageRequestEvent;
use Civi\Funding\Mock\RequestContext\TestRequestContext;
use Civi\Funding\Page\AbstractRemoteControllerPage;
use Civi\RemoteTools\Exception\ResolveContactIdFailedException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

/**
 * @covers \Civi\Funding\EventSubscriber\Remote\RemotePageRequestSubscriber
 */
final class RemotePageRequestSubscriberTest extends TestCase {

  /**
   * @var \Civi\Funding\Contact\FundingRemoteContactIdResolverInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $remoteContactIdResolverMock;

  private TestRequestContext $requestContext;

  private RemotePageRequestSubscriber $subscriber;

  protected function setUp(): void {
    $this->remoteContactIdResolverMock = $this->createMock(FundingRemoteContactIdResolverInterface::class);
    $this->requestContext = TestRequestContext::newRemote(0);
    parent::setUp();
    $this->subscriber = new RemotePageRequestSubscriber(
      $this->remoteContactIdResolverMock,
      $this->requestContext,
    );
  }

  public function testGetSubscribedEvents(): void {
    $expectedSubscriptions = [
      RemotePageRequestEvent::class => 'onRemotePageRequest',
    ];

    static::assertEquals($expectedSubscriptions, $this->subscriber::getSubscribedEvents());

    foreach ($expectedSubscriptions as $method) {
      static::assertTrue(method_exists(get_class($this->subscriber), $method));
    }
  }

  public function test(): void {
    $pageMock = $this->createMock(AbstractRemoteControllerPage::class);
    $request = new Request();
    $request->headers->set('X-Civi-Remote-Contact-Id', 'abc');
    $event = new RemotePageRequestEvent($pageMock, $request);

    $this->remoteContactIdResolverMock->method('getContactId')
      ->with('abc')
      ->willReturn(123);
    $this->subscriber->onRemotePageRequest($event);
    static::assertSame(123, $this->requestContext->getContactId());
  }

  public function testRemoteContactIdMissing(): void {
    $pageMock = $this->createMock(AbstractRemoteControllerPage::class);
    $request = new Request();
    $event = new RemotePageRequestEvent($pageMock, $request);

    $this->expectException(BadRequestHttpException::class);
    $this->expectExceptionMessage('Remote contact ID missing');
    $this->subscriber->onRemotePageRequest($event);
  }

  public function testRemoteContactIdResolveFailed(): void {
    $pageMock = $this->createMock(AbstractRemoteControllerPage::class);
    $request = new Request();
    $request->headers->set('X-Civi-Remote-Contact-Id', 'abc');
    $event = new RemotePageRequestEvent($pageMock, $request);

    $resolveException = new ResolveContactIdFailedException();
    $this->remoteContactIdResolverMock->method('getContactId')
      ->with('abc')
      ->willThrowException($resolveException);

    $this->expectExceptionObject(new UnauthorizedHttpException(
      'funding-remote',
      'Unknown remote contact ID',
      $resolveException
    ));
    $this->subscriber->onRemotePageRequest($event);
  }

}
