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

use Civi\Api4\FundingApplicationResourcesItem;
use Civi\Api4\Generic\Result;
use Civi\Funding\Entity\ApplicationResourcesItemEntity;
use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\Api4\Query\Comparison;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\ApplicationProcess\ApplicationResourcesItemManager
 * @covers \Civi\Funding\ApplicationProcess\AbstractFinancePlanItemManager
 */
final class ApplicationResourcesItemManagerTest extends TestCase {

  /**
   * @var \Civi\RemoteTools\Api4\Api4Interface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $api4Mock;

  private ApplicationResourcesItemManager $applicationResourcesItemManager;

  protected function setUp(): void {
    parent::setUp();
    $this->api4Mock = $this->createMock(Api4Interface::class);
    $this->applicationResourcesItemManager = new ApplicationResourcesItemManager($this->api4Mock);
  }

  public function testGetByApplicationProcessId(): void {
    $item = $this->createApplicationResourcesItem();

    $this->api4Mock->expects(static::once())->method('getEntities')->with(
      FundingApplicationResourcesItem::getEntityName(),
      Comparison::new('application_process_id', '=', 2)
    )->willReturn(new Result([$item->toArray()]));

    static::assertEquals(
      ['testIdentifier' => $item],
      $this->applicationResourcesItemManager->getByApplicationProcessId(2)
    );
  }

  public function testDelete(): void {
    $item = $this->createApplicationResourcesItem();

    $this->api4Mock->expects(static::once())->method('deleteEntity')
      ->with(FundingApplicationResourcesItem::getEntityName(), $item->getId());

    $this->applicationResourcesItemManager->delete($item);
  }

  public function testUpdateAll(): void {
    $deletedItem = $this->createApplicationResourcesItem(11, 'deleted', '/deleted');
    $item = $this->createApplicationResourcesItem(12, 'updated', '/before');
    $updatedItem = $this->createApplicationResourcesItem(12, 'updated', '/after');
    $updatedItem
      ->setAmount(12345)
      ->setType('updatedType')
      ->setProperties(['foo' => 'baz']);
    $newItem = $this->createApplicationResourcesItem(NULL, 'new', '/new');

    $this->api4Mock->expects(static::once())->method('getEntities')->with(
      FundingApplicationResourcesItem::getEntityName(),
      Comparison::new('application_process_id', '=', 2)
    )->willReturn(new Result([$deletedItem->toArray(), $item->toArray()]));

    $this->api4Mock->expects(static::once())->method('updateEntity')
      ->with(FundingApplicationResourcesItem::getEntityName(), $updatedItem->getId(), $updatedItem->toArray())
      ->willReturn(new Result([$updatedItem->toArray()]));

    $this->api4Mock->expects(static::once())->method('createEntity')
      ->with(FundingApplicationResourcesItem::getEntityName(), $newItem->toArray())
      ->willReturn(new Result([$newItem->toArray() + ['id' => 13]]));

    $this->api4Mock->expects(static::once())->method('deleteEntity')
      ->with(FundingApplicationResourcesItem::getEntityName(), $deletedItem->getId());

    $this->applicationResourcesItemManager->updateAll(2, [$updatedItem, $newItem]);
    static::assertSame(13, $newItem->getId());
  }

  public function testSaveNew(): void {
    $item = $this->createApplicationResourcesItem(NULL);
    $this->api4Mock->expects(static::once())->method('createEntity')
      ->with(FundingApplicationResourcesItem::getEntityName(), $item->toArray())
      ->willReturn(new Result([$item->toArray() + ['id' => 11]]));

    $this->applicationResourcesItemManager->save($item);
    static::assertSame(11, $item->getId());
  }

  public function testSaveExisting(): void {
    $item = $this->createApplicationResourcesItem();
    $this->api4Mock->expects(static::once())->method('updateEntity')
      ->with(FundingApplicationResourcesItem::getEntityName(), $item->getId(), $item->toArray())
      ->willReturn(new Result([$item->toArray()]));

    $this->applicationResourcesItemManager->save($item);
  }

  /**
   * @param non-empty-string $identifier
   * @param non-empty-string $dataPointer
   */
  private function createApplicationResourcesItem(?int $id = 12,
    string $identifier = 'testIdentifier',
    string $dataPointer = '/test'
  ): ApplicationResourcesItemEntity {
    $values = [
      'application_process_id' => 2,
      'identifier' => $identifier,
      'type' => 'testType',
      'amount' => 1.23,
      'properties' => ['foo' => 'bar'],
      'data_pointer' => $dataPointer,
    ];
    if (NULL !== $id) {
      $values['id'] = $id;
    }

    return ApplicationResourcesItemEntity::fromArray($values);
  }

}
