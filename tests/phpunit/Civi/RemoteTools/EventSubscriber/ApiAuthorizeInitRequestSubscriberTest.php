<?php
/**
 * @noinspection PhpUnhandledExceptionInspection
 */

declare(strict_types = 1);

namespace Civi\RemoteTools\EventSubscriber;

use Civi\API\Event\AuthorizeEvent;
use Civi\RemoteTools\Api4\Action\EventGetAction;
use Civi\RemoteTools\Event\AuthorizeApiRequestEvent;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
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
   * @var \PHPUnit\Framework\MockObject\MockObject&EventDispatcherInterface
   */
  private MockObject $eventDispatcherMock;

  protected function setUp(): void {
    parent::setUp();

    $this->eventMock = $this->createMock(AuthorizeEvent::class);
    $this->requestMock = $this->createMock(EventGetAction::class);
    $this->requestMock->method('getInitRequestEventClass')->willReturn(AuthorizeApiRequestEvent::class);
    $this->requestMock->method('getInitRequestEventName')->willReturn('test.request.init');
    $this->eventMock->method('getApiRequest')->willReturn($this->requestMock);
    $this->eventDispatcherMock = $this->createMock(EventDispatcherInterface::class);

    $this->subscriber = new ApiAuthorizeInitRequestSubscriber();
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

    $this->subscriber->onApiAuthorize($this->eventMock, 'civi.api.authorize', $this->eventDispatcherMock);
  }

  public function testOnApiAuthorizeRequiredExtraParamMissing(): void {
    $this->requestMock->expects(static::once())->method('getRequiredExtraParams')->willReturn(['foo']);

    $this->eventDispatcherMock->expects(static::once())->method('dispatch')
      ->with('test.request.init', static::isInstanceOf(AuthorizeApiRequestEvent::class))
      ->willReturnCallback(function (string $eventName, AuthorizeApiRequestEvent $event) {
        static::assertSame($this->requestMock, $event->getApiRequest());
      });

    $this->requestMock->expects(static::once())->method('hasExtraParam')->with('foo')->willReturn(FALSE);

    static::expectException(\API_Exception::class);
    static::expectExceptionMessage('Required extra param "foo" is missing');
    $this->subscriber->onApiAuthorize($this->eventMock, 'civi.api.authorize', $this->eventDispatcherMock);
  }

}
