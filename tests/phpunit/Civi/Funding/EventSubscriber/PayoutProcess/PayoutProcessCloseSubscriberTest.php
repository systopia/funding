<?php
/*
 * Copyright (C) 2023 SYSTOPIA GmbH
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

namespace Civi\Funding\EventSubscriber\PayoutProcess;

use Civi\Funding\EntityFactory\DrawdownFactory;
use Civi\Funding\EntityFactory\PayoutProcessFactory;
use Civi\Funding\Event\PayoutProcess\DrawdownAcceptedEvent;
use Civi\Funding\PayoutProcess\PayoutProcessManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\EventSubscriber\PayoutProcess\PayoutProcessCloseSubscriber
 */
final class PayoutProcessCloseSubscriberTest extends TestCase {

  /**
   * @var \Civi\Funding\PayoutProcess\PayoutProcessManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $payoutProcessManagerMock;

  private PayoutProcessCloseSubscriber $subscriber;

  protected function setUp(): void {
    parent::setUp();
    $this->payoutProcessManagerMock = $this->createMock(PayoutProcessManager::class);
    $this->subscriber = new PayoutProcessCloseSubscriber($this->payoutProcessManagerMock);
  }

  public function testGetSubscribedEvents(): void {
    $expectedSubscriptions = [
      DrawdownAcceptedEvent::class => 'onAccepted',
    ];

    static::assertEquals($expectedSubscriptions, $this->subscriber::getSubscribedEvents());

    foreach ($expectedSubscriptions as $method) {
      static::assertTrue(method_exists($this->subscriber, $method));
    }
  }

  public function testOnAccepted(): void {
    $payoutProcess = PayoutProcessFactory::create(['amount_total' => 12.34]);
    $drawdown = DrawdownFactory::create(['status' => 'accepted']);

    $this->payoutProcessManagerMock->method('get')
      ->with($drawdown->getPayoutProcessId())
      ->willReturn($payoutProcess);
    $this->payoutProcessManagerMock->method('getAmountAccepted')
      ->with($payoutProcess)
      ->willReturn(12.33);

    $this->payoutProcessManagerMock->expects(static::never())->method('close');
    $this->subscriber->onAccepted(new DrawdownAcceptedEvent($drawdown));
  }

  public function testOnAcceptedAmountReached(): void {
    $payoutProcess = PayoutProcessFactory::create(['amount_total' => 12.34]);
    $drawdown = DrawdownFactory::create(['status' => 'accepted']);

    $this->payoutProcessManagerMock->method('get')
      ->with($drawdown->getPayoutProcessId())
      ->willReturn($payoutProcess);
    $this->payoutProcessManagerMock->method('getAmountAccepted')
      ->with($payoutProcess)
      ->willReturn(12.34);

    $this->payoutProcessManagerMock->expects(static::once())->method('close')
      ->with($payoutProcess);
    $this->subscriber->onAccepted(new DrawdownAcceptedEvent($drawdown));
  }

}
