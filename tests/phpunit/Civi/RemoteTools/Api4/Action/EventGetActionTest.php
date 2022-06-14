<?php
/**
 * @noinspection PhpUnhandledExceptionInspection
 * @noinspection PropertyAnnotationInspection
 */

declare(strict_types = 1);

namespace Civi\RemoteTools\Api4\Action;

use Civi\Api4\Generic\Result;
use Civi\RemoteTools\Event\GetEvent;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub\ReturnCallback;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @covers \Civi\RemoteTools\Api4\Action\EventGetAction
 */
final class EventGetActionTest extends TestCase {

  /**
   * @var \PHPUnit\Framework\MockObject\MockObject&\Symfony\Contracts\EventDispatcher\EventDispatcherInterface
   */
  private MockObject $eventDispatcherMock;

  private EventGetAction $eventGetAction;

  protected function setUp(): void {
    parent::setUp();
    $this->eventDispatcherMock = $this->createMock(EventDispatcherInterface::class);
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
