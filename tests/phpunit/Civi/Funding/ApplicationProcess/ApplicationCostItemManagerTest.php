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

namespace Civi\Funding\ApplicationProcess;

use Civi\Api4\FundingApplicationCostItem;
use Civi\Api4\Generic\DAOCreateAction;
use Civi\Api4\Generic\DAODeleteAction;
use Civi\Api4\Generic\DAOGetAction;
use Civi\Api4\Generic\DAOUpdateAction;
use Civi\Api4\Generic\Result;
use Civi\Funding\Entity\ApplicationCostItemEntity;
use Civi\RemoteTools\Api4\Api4Interface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\ApplicationProcess\ApplicationCostItemManager
 */
final class ApplicationCostItemManagerTest extends TestCase {

  /**
   * @var \Civi\RemoteTools\Api4\Api4Interface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $api4Mock;

  private ApplicationCostItemManager $applicationCostItemManager;

  protected function setUp(): void {
    parent::setUp();
    $this->api4Mock = $this->createMock(Api4Interface::class);
    $this->applicationCostItemManager = new ApplicationCostItemManager($this->api4Mock);
  }

  public function testGetByApplicationProcessId(): void {
    $item = $this->createApplicationCostItem();

    $this->api4Mock->expects(static::once())->method('executeAction')
      ->willReturnCallback(function (DAOGetAction $action) use ($item) {
        static::assertSame(FundingApplicationCostItem::_getEntityName(), $action->getEntityName());
        static::assertSame([['application_process_id', '=', 2, FALSE]], $action->getWhere());

        return new Result([$item->toArray()]);
      });

    static::assertEquals(['testIdentifier' => $item], $this->applicationCostItemManager->getByApplicationProcessId(2));
  }

  public function testDelete(): void {
    $item = $this->createApplicationCostItem();

    $this->api4Mock->expects(static::once())->method('executeAction')
      ->willReturnCallback(function (DAODeleteAction $action) {
          static::assertSame(FundingApplicationCostItem::_getEntityName(), $action->getEntityName());
          static::assertSame([['id', '=', 12]], $action->getWhere());

          return new Result();
      });

    $this->applicationCostItemManager->delete($item);
  }

  public function testUpdateAll(): void {
    $deletedItem = $this->createApplicationCostItem(11, 'deleted');
    $item = $this->createApplicationCostItem(12, 'updated');
    $updatedItem = $this->createApplicationCostItem(12, 'updated');
    $updatedItem->setProperties(['foo' => 'baz']);
    $newItem = $this->createApplicationCostItem(NULL, 'new');

    $this->api4Mock->expects(static::exactly(4))->method('executeAction')
      ->withConsecutive(
        [
          static::callback(function (DAOGetAction $action) {
            static::assertSame(FundingApplicationCostItem::_getEntityName(), $action->getEntityName());
            static::assertSame([['application_process_id', '=', 2, FALSE]], $action->getWhere());

            return TRUE;
          }),
        ],
        [
          static::callback(function (DAOUpdateAction $action) use ($updatedItem) {
            static::assertSame(FundingApplicationCostItem::_getEntityName(), $action->getEntityName());
            static::assertSame($updatedItem->toArray(), $action->getValues());

            return TRUE;
          }),
        ],
        [
          static::callback(function (DAOCreateAction $action) use ($newItem) {
            // Callbacks are executed twice. On second call $newItem has attribute 'id'.
            static $values;
            $values ??= $newItem->toArray();
            static::assertSame(FundingApplicationCostItem::_getEntityName(), $action->getEntityName());
            static::assertSame($values, $action->getValues());

            return TRUE;
          }),
        ],
        [
          static::callback(function (DAODeleteAction $action) {
            static::assertSame(FundingApplicationCostItem::_getEntityName(), $action->getEntityName());
            static::assertSame([['id', '=', 11]], $action->getWhere());

            return TRUE;
          }),
        ],
      )
      ->willReturnOnConsecutiveCalls(
        new Result([$deletedItem->toArray(), $item->toArray()]),
        new Result([$updatedItem->toArray()]),
        new Result([$newItem->toArray() + ['id' => 13]]),
        new Result(),
      );

    $this->applicationCostItemManager->updateAll(2, [$updatedItem, $newItem]);
    static::assertSame(13, $newItem->getId());
  }

  public function testSaveNew(): void {
    $item = $this->createApplicationCostItem(NULL);
    $this->api4Mock->expects(static::once())->method('executeAction')
      ->willReturnCallback(function (DAOCreateAction $action) use ($item) {
        static::assertSame(FundingApplicationCostItem::_getEntityName(), $action->getEntityName());
        static::assertSame($item->toArray(), $action->getValues());

        return new Result([$item->toArray() + ['id' => 11]]);
      });

    $this->applicationCostItemManager->save($item);
    static::assertSame(11, $item->getId());
  }

  public function testSaveExisting(): void {
    $item = $this->createApplicationCostItem();
    $this->api4Mock->expects(static::once())->method('executeAction')
      ->willReturnCallback(function (DAOUpdateAction $action) use ($item) {
        static::assertSame(FundingApplicationCostItem::_getEntityName(), $action->getEntityName());
        static::assertSame($item->toArray(), $action->getValues());

        return new Result([$item->toArray()]);
      });

    $this->applicationCostItemManager->save($item);
  }

  private function createApplicationCostItem(?int $id = 12,
    string $identifier = 'testIdentifier'
  ): ApplicationCostItemEntity {
    $values = [
      'application_process_id' => 2,
      'identifier' => $identifier,
      'type' => 'testType',
      'amount' => 1.23,
      'properties' => ['foo' => 'bar'],
    ];
    if (NULL !== $id) {
      $values['id'] = $id;
    }

    return ApplicationCostItemEntity::fromArray($values);
  }

}
