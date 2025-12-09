<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
 *
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License as published by the Free
 * Software Foundation, either version 3 of the License, or (at your option) any
 * later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types = 1);

namespace Civi\Funding\EventSubscriber\ClearingProcess;

use Civi\Funding\ClearingProcess\ClearingCostItemManager;
use Civi\Funding\ClearingProcess\ClearingResourcesItemManager;
use Civi\Funding\EntityFactory\ClearingProcessBundleFactory;
use Civi\Funding\EntityFactory\ClearingProcessFactory;
use Civi\Funding\Event\ClearingProcess\ClearingProcessUpdatedEvent;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\EventSubscriber\ClearingProcess\ClearingProcessRejectSubscriber
 */
final class ClearingProcessRejectSubscriberTest extends TestCase {

  private ClearingCostItemManager&MockObject $clearingCostItemManagerMock;

  private ClearingResourcesItemManager&MockObject $clearingResourcesItemManagerMock;

  private ClearingProcessRejectSubscriber $subscriber;

  protected function setUp(): void {
    parent::setUp();
    $this->clearingCostItemManagerMock = $this->createMock(ClearingCostItemManager::class);
    $this->clearingResourcesItemManagerMock = $this->createMock(ClearingResourcesItemManager::class);
    $this->subscriber = new ClearingProcessRejectSubscriber(
      $this->clearingCostItemManagerMock,
      $this->clearingResourcesItemManagerMock
    );
  }

  public function testGetSubscribedEvents(): void {
    $expectedSubscriptions = [ClearingProcessUpdatedEvent::class => 'onUpdated'];

    self::assertEquals($expectedSubscriptions, $this->subscriber::getSubscribedEvents());

    foreach ($expectedSubscriptions as $method) {
      static::assertTrue(method_exists(get_class($this->subscriber), $method));
    }
  }

  public function testRejected(): void {
    $previousClearingProcess = ClearingProcessFactory::create(['status' => 'review']);
    $clearingProcessBundle = ClearingProcessBundleFactory::create(['status' => 'rejected']);

    $this->clearingCostItemManagerMock->expects(static::once())->method('rejectByClearingProcessId')
      ->with($clearingProcessBundle->getClearingProcess()->getId());
    $this->clearingResourcesItemManagerMock->expects(static::once())->method('rejectByClearingProcessId')
      ->with($clearingProcessBundle->getClearingProcess()->getId());

    $this->subscriber->onUpdated(new ClearingProcessUpdatedEvent($previousClearingProcess, $clearingProcessBundle));
  }

  public function testNotRejected(): void {
    $previousClearingProcess = ClearingProcessFactory::create(['status' => 'review']);
    $clearingProcessBundle = ClearingProcessBundleFactory::create(['status' => 'accepted']);

    $this->clearingCostItemManagerMock->expects(static::never())->method('rejectByClearingProcessId');
    $this->clearingResourcesItemManagerMock->expects(static::never())->method('rejectByClearingProcessId');

    $this->subscriber->onUpdated(new ClearingProcessUpdatedEvent($previousClearingProcess, $clearingProcessBundle));
  }

}
