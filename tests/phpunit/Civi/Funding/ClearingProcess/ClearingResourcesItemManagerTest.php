<?php
/*
 * Copyright (C) 2024 SYSTOPIA GmbH
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

namespace Civi\Funding\ClearingProcess;

use Civi\Api4\FundingClearingResourcesItem;
use Civi\Api4\Generic\Result;
use Civi\Funding\EntityFactory\ClearingResourcesItemFactory;
use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\Api4\Query\Comparison;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\ClearingProcess\ClearingResourcesItemManager
 * @covers \Civi\Funding\ClearingProcess\AbstractClearingItemManager
 */
final class ClearingResourcesItemManagerTest extends TestCase {

  /**
   * @var \Civi\RemoteTools\Api4\Api4Interface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $api4Mock;

  private ClearingResourcesItemManager $itemManager;

  protected function setUp(): void {
    parent::setUp();
    $this->api4Mock = $this->createMock(Api4Interface::class);
    $this->itemManager = new ClearingResourcesItemManager($this->api4Mock);
  }

  public function testDelete(): void {
    $item = ClearingResourcesItemFactory::create(['id' => 22]);

    $this->api4Mock->expects(static::once())->method('deleteEntity')
      ->with(FundingClearingResourcesItem::getEntityName(), 22);

    $this->itemManager->delete($item);
  }

  public function testGetByApplicationProcessId(): void {
    $item = ClearingResourcesItemFactory::create(['id' => 22]);
    $this->api4Mock->method('getEntities')->with(
      FundingClearingResourcesItem::getEntityName(),
      Comparison::new('application_resources_item_id.application_process_id', '=', 12)
    )->willReturn(new Result([$item->toArray()]));

    static::assertEquals([22 => $item], $this->itemManager->getByApplicationProcessId(12));
  }

  public function testGetByResourcesItemId(): void {
    $item = ClearingResourcesItemFactory::create([
      'id' => 22,
      'application_resources_item_id' => 33,
    ]);
    $this->api4Mock->method('getEntities')->with(
      FundingClearingResourcesItem::getEntityName(),
      Comparison::new('application_resources_item_id', '=', 33)
    )->willReturn(new Result([$item->toArray()]));

    static::assertEquals([22 => $item], $this->itemManager->getByResourcesItemId(33));
  }

  public function testSaveNew(): void {
    $item = ClearingResourcesItemFactory::create();
    $this->api4Mock->expects(static::once())->method('createEntity')
      ->with(FundingClearingResourcesItem::getEntityName(), $item->toArray())
      ->willReturn(new Result([$item->toArray() + ['id' => 11]]));

    $this->itemManager->save($item);
    static::assertSame(11, $item->getId());
  }

  public function testSaveExisting(): void {
    $item = ClearingResourcesItemFactory::create(['id' => 22]);
    $this->api4Mock->expects(static::once())->method('updateEntity')
      ->with(FundingClearingResourcesItem::getEntityName(), 22, $item->toArray())
      ->willReturn(new Result([$item->toArray()]));

    $this->itemManager->save($item);
  }

}
