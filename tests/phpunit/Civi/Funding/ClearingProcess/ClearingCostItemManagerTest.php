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

use Civi\Api4\FundingClearingCostItem;
use Civi\Api4\Generic\Result;
use Civi\Funding\EntityFactory\ClearingCostItemFactory;
use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\Api4\Query\Comparison;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\ClearingProcess\ClearingCostItemManager
 * @covers \Civi\Funding\ClearingProcess\AbstractClearingItemManager
 */
final class ClearingCostItemManagerTest extends TestCase {

  /**
   * @var \Civi\RemoteTools\Api4\Api4Interface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $api4Mock;

  /**
   * @var \Civi\Funding\ClearingProcess\ClearingExternalFileManagerInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $externalFileManagerMock;

  private ClearingCostItemManager $itemManager;

  protected function setUp(): void {
    parent::setUp();
    $this->api4Mock = $this->createMock(Api4Interface::class);
    $this->externalFileManagerMock = $this->createMock(ClearingExternalFileManagerInterface::class);
    $this->itemManager = new ClearingCostItemManager($this->api4Mock, $this->externalFileManagerMock);
  }

  public function testGetByFinancePlanItemId(): void {
    $this->api4Mock->expects(static::once())->method('countEntities')
      ->with('FundingClearingCostItem', Comparison::new('application_cost_item_id', '=', 2))
      ->willReturn(3);

    static::assertSame(3, $this->itemManager->countByFinancePlanItemId(2));
  }

  public function testDelete(): void {
    $item = ClearingCostItemFactory::create(['id' => 22]);

    $this->externalFileManagerMock->expects(static::once())->method('deleteFileByClearingItem')
      ->with($item);
    $this->api4Mock->expects(static::once())->method('deleteEntity')
      ->with(FundingClearingCostItem::getEntityName(), 22);

    $this->itemManager->delete($item);
  }

  public function testGetByApplicationProcessId(): void {
    $item = ClearingCostItemFactory::create(['id' => 22]);
    $this->api4Mock->method('getEntities')->with(
      FundingClearingCostItem::getEntityName(),
      Comparison::new('application_cost_item_id.application_process_id', '=', 12)
    )->willReturn(new Result([$item->toArray()]));

    static::assertEquals([22 => $item], $this->itemManager->getByApplicationProcessId(12));
  }

  public function testGetByCostItemId(): void {
    $item = ClearingCostItemFactory::create([
      'id' => 22,
      'application_cost_item_id' => 33,
    ]);
    $this->api4Mock->method('getEntities')->with(
      FundingClearingCostItem::getEntityName(),
      Comparison::new('application_cost_item_id', '=', 33)
    )->willReturn(new Result([$item->toArray()]));

    static::assertEquals([22 => $item], $this->itemManager->getByCostItemId(33));
  }

  public function testSaveNew(): void {
    $item = ClearingCostItemFactory::create();
    $this->api4Mock->expects(static::once())->method('createEntity')
      ->with(FundingClearingCostItem::getEntityName(), $item->toArray())
      ->willReturn(new Result([$item->toArray() + ['id' => 11]]));

    $this->itemManager->save($item);
    static::assertSame(11, $item->getId());
  }

  public function testSaveExisting(): void {
    $item = ClearingCostItemFactory::create(['id' => 22]);
    $this->api4Mock->expects(static::once())->method('updateEntity')
      ->with(FundingClearingCostItem::getEntityName(), 22, $item->toArray())
      ->willReturn(new Result([$item->toArray()]));

    $this->itemManager->save($item);
  }

}
