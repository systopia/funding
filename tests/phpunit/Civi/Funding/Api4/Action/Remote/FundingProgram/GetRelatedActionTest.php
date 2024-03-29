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

namespace Civi\Funding\Api4\Action\Remote\FundingProgram;

use Civi\Api4\Generic\Result;
use Civi\Core\CiviEventDispatcherInterface;
use Civi\Funding\Event\Remote\FundingDAOGetEvent;
use Civi\Funding\Traits\CreateMockTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\Api4\Action\Remote\FundingProgram\GetRelatedAction
 */
final class GetRelatedActionTest extends TestCase {

  use CreateMockTrait;

  /**
   * @var \PHPUnit\Framework\MockObject\MockObject&\Civi\Core\CiviEventDispatcherInterface
   */
  private MockObject $eventDispatcherMock;

  private GetRelatedAction $action;

  protected function setUp(): void {
    parent::setUp();
    $this->eventDispatcherMock = $this->createMock(CiviEventDispatcherInterface::class);
    $this->action = $this->createApi4ActionMock(GetRelatedAction::class, $this->eventDispatcherMock);
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
          FundingDAOGetEvent::getEventName('RemoteFundingProgram', 'getRelated'),
          static::callback(
            function (FundingDAOGetEvent $event): bool {
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
          FundingDAOGetEvent::getEventName('RemoteFundingProgram'),
          static::isInstanceOf(FundingDAOGetEvent::class),
        ],
        [
          FundingDAOGetEvent::getEventName(),
          static::isInstanceOf(FundingDAOGetEvent::class),
        ]
      );

    $this->action->_run($result);
    static::assertSame(23, $result->rowCount);
    static::assertSame([['foo' => 'bar']], $result->getArrayCopy());
  }

}
