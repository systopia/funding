<?php
/**
 * @noinspection PhpUnhandledExceptionInspection
 * @noinspection PropertyAnnotationInspection
 */

declare(strict_types = 1);

namespace Civi\RemoteTools\Api4\Action;

use Civi\Api4\Generic\Result;
use Civi\Core\CiviEventDispatcher;
use Civi\RemoteTools\Event\DAOGetEvent;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub\ReturnCallback;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\RemoteTools\Api4\Action\EventDAOGetAction
 */
final class EventDAOGetActionTest extends TestCase {

  /**
   * @var \PHPUnit\Framework\MockObject\MockObject&\Civi\Core\CiviEventDispatcher
   */
  private MockObject $eventDispatcherMock;

  private EventDAOGetAction $eventGetAction;

  protected function setUp(): void {
    parent::setUp();
    $this->eventDispatcherMock = $this->createMock(CiviEventDispatcher::class);
    $this->eventGetAction = new EventDAOGetAction(
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
        [DAOGetEvent::getEventName('test', 'action'), static::isInstanceOf(DAOGetEvent::class)],
        [DAOGetEvent::getEventName('test'), static::isInstanceOf(DAOGetEvent::class)],
        [DAOGetEvent::getEventName(), static::isInstanceOf(DAOGetEvent::class)]
      )
      ->willReturnOnConsecutiveCalls(
        new ReturnCallback(function (string $eventName, DAOGetEvent $event) {
          $event->setRowCount(123);
          $event->addRecord(['foo' => 'bar']);
        })
      );

    $this->eventGetAction->_run($result);

    static::assertSame(123, $result->rowCount);
    static::assertSame([['foo' => 'bar']], $result->getArrayCopy());
  }

}
