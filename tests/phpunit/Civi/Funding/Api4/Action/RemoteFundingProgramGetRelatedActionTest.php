<?php
/**
 * @noinspection PhpUnhandledExceptionInspection
 * @noinspection PropertyAnnotationInspection
 */

declare(strict_types = 1);

namespace Civi\Funding\Api4\Action;

use Civi\Api4\Generic\Result;
use Civi\Core\CiviEventDispatcher;
use Civi\Funding\Event\RemoteFundingDAOGetEvent;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\Api4\Action\RemoteFundingProgramGetRelatedAction
 */
final class RemoteFundingProgramGetRelatedActionTest extends TestCase {

  /**
   * @var \PHPUnit\Framework\MockObject\MockObject&\Civi\Core\CiviEventDispatcher
   */
  private MockObject $eventDispatcherMock;

  private RemoteFundingProgramGetRelatedAction $action;

  protected function setUp(): void {
    parent::setUp();
    $this->eventDispatcherMock = $this->createMock(CiviEventDispatcher::class);
    $this->action = new RemoteFundingProgramGetRelatedAction($this->eventDispatcherMock);
  }

  public function testRun(): void {
    $result = new Result();

    $this->action->setRemoteContactId('00');
    $this->action->setExtraParam('contactId', 11);
    $this->action->setId(12);
    $this->action->setType('test');

    $this->eventDispatcherMock->expects(static::exactly(3))
      ->method('dispatch')
      ->withConsecutive(
        [
          RemoteFundingDAOGetEvent::getEventName('RemoteFundingProgram', 'getRelated'),
          static::callback(
            function (RemoteFundingDAOGetEvent $event): bool {
              static::assertSame(11, $event->getContactId());
              static::assertSame([
                [
                  'FundingProgramRelationship AS relationship',
                  'INNER',
                  ['id', '=', 'relationship.id_b'],
                  ['relationship.type', '=', "'test'"],
                ],
              ], $event->getJoin());
              static::assertSame([
                [
                  'relationship.id_a',
                  '=',
                  12,
                  FALSE,
                ],
              ], $event->getWhere());

              $event->setRowCount(23);
              $event->addRecord(['foo' => 'bar']);

              return TRUE;
            }),
        ],
        [
          RemoteFundingDAOGetEvent::getEventName('RemoteFundingProgram'),
          static::isInstanceOf(RemoteFundingDAOGetEvent::class),
        ],
        [
          RemoteFundingDAOGetEvent::getEventName(),
          static::isInstanceOf(RemoteFundingDAOGetEvent::class),
        ]
      );

    $this->action->_run($result);
    static::assertSame(23, $result->rowCount);
    static::assertSame([['foo' => 'bar']], $result->getArrayCopy());
  }

}
