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

namespace Civi\Funding\EventSubscriber\ClearingProcess;

use Civi\Funding\ClearingProcess\ClearingProcessManager;
use Civi\Funding\Entity\ClearingProcessEntityBundle;
use Civi\Funding\EntityFactory\ApplicationProcessBundleFactory;
use Civi\Funding\EntityFactory\ApplicationProcessFactory;
use Civi\Funding\EntityFactory\ClearingProcessFactory;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessUpdatedEvent;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\EventSubscriber\ClearingProcess\ApplicationProcessWithdrawSubscriber
 */
final class ApplicationProcessWithdrawSubscriberTest extends TestCase {

  /**
   * @var \Civi\Funding\ClearingProcess\ClearingProcessManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $clearingProcessManagerMock;

  /**
   * @var \Civi\Funding\EventSubscriber\ClearingProcess\ApplicationProcessWithdrawSubscriber
   */
  private ApplicationProcessWithdrawSubscriber $subscriber;

  protected function setUp(): void {
    parent::setUp();
    $this->clearingProcessManagerMock = $this->createMock(ClearingProcessManager::class);
    $this->subscriber = new ApplicationProcessWithdrawSubscriber($this->clearingProcessManagerMock);
  }

  public function testGetSubscribedEvents(): void {
    $expectedSubscriptions = [ApplicationProcessUpdatedEvent::class => 'onUpdated'];

    static::assertEquals($expectedSubscriptions, $this->subscriber::getSubscribedEvents());

    foreach ($expectedSubscriptions as $method) {
      static::assertTrue(method_exists(get_class($this->subscriber), $method));
    }
  }

  public function testWithdrawn(): void {
    $event = $this->createEvent('complete', 'withdrawn');
    $clearingProcess = ClearingProcessFactory::create(['status' => 'accepted']);
    $this->clearingProcessManagerMock->method('getByApplicationProcessId')
      ->with($event->getApplicationProcess()->getId())
      ->willReturn($clearingProcess);

    $this->clearingProcessManagerMock->expects(static::once())->method('update')
      ->with(static::callback(
        fn(ClearingProcessEntityBundle $clearingProcessBundle) =>
          $clearingProcessBundle->getClearingProcess() === $clearingProcess
          && 'rejected' === $clearingProcess->getStatus()
      ));

    $this->subscriber->onUpdated($event);
  }

  public function testRejected(): void {
    $event = $this->createEvent('complete', 'rejected');
    $clearingProcess = ClearingProcessFactory::create(['status' => 'review']);
    $this->clearingProcessManagerMock->method('getByApplicationProcessId')
      ->with($event->getApplicationProcess()->getId())
      ->willReturn($clearingProcess);

    $this->clearingProcessManagerMock->expects(static::once())->method('update')
      ->with(static::callback(
        fn(ClearingProcessEntityBundle $clearingProcessBundle) =>
          $clearingProcessBundle->getClearingProcess() === $clearingProcess
          && 'rejected' === $clearingProcess->getStatus()
      ));

    $this->subscriber->onUpdated($event);
  }

  public function testWithdrawnNoClearingProcessExists(): void {
    $event = $this->createEvent('eligible', 'withdrawn');
    $this->clearingProcessManagerMock->expects(static::once())->method('getByApplicationProcessId')
      ->with($event->getApplicationProcess()->getId())
      ->willReturn(NULL);

    $this->subscriber->onUpdated($event);
  }

  public function testAlreadyWithdrawn(): void {
    $event = $this->createEvent('withdrawn', 'withdrawn');
    $this->clearingProcessManagerMock->expects(static::never())->method('getByApplicationProcessId');
    $this->subscriber->onUpdated($event);
  }

  public function testClearingProcessNotStarted(): void {
    $event = $this->createEvent('eligible', 'withdrawn');
    $clearingProcess = ClearingProcessFactory::create(['status' => 'not-started']);
    $this->clearingProcessManagerMock->method('getByApplicationProcessId')
      ->with($event->getApplicationProcess()->getId())
      ->willReturn($clearingProcess);

    $this->clearingProcessManagerMock->expects(static::never())->method('update');
    $this->subscriber->onUpdated($event);
    static::assertSame('not-started', $clearingProcess->getStatus());
  }

  public function testOtherStatusChange(): void {
    $event = $this->createEvent('eligible', 'complete');
    $this->clearingProcessManagerMock->expects(static::never())->method('getByApplicationProcessId');
    $this->subscriber->onUpdated($event);
  }

  private function createEvent(string $oldStatus, string $newStatus): ApplicationProcessUpdatedEvent {
    return new ApplicationProcessUpdatedEvent(
      ApplicationProcessFactory::createApplicationProcess([
        'status' => $oldStatus,
        'is_rejected' => 'rejected' === $oldStatus,
        'is_withdrawn' => 'withdrawn' === $oldStatus,
      ]),
      ApplicationProcessBundleFactory::createApplicationProcessBundle([
        'status' => $newStatus,
        'is_rejected' => 'rejected' === $newStatus,
        'is_withdrawn' => 'withdrawn' === $newStatus,
      ]),
    );
  }

}
