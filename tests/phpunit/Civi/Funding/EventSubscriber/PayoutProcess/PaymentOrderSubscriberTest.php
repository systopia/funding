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
use Civi\Funding\Event\PayoutProcess\DrawdownAcceptedEvent;
use Civi\Funding\PayoutProcess\PaymentOrderCreator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\EventSubscriber\PayoutProcess\PaymentOrderSubscriber
 */
final class PaymentOrderSubscriberTest extends TestCase {

  /**
   * @var \Civi\Funding\PayoutProcess\PaymentOrderCreator&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $paymentOrderCreatorMock;

  private PaymentOrderSubscriber $subscriber;

  protected function setUp(): void {
    parent::setUp();
    $this->paymentOrderCreatorMock = $this->createMock(PaymentOrderCreator::class);
    $this->subscriber = new PaymentOrderSubscriber($this->paymentOrderCreatorMock);
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
    $drawdown = DrawdownFactory::create(['status' => 'accepted']);

    $this->paymentOrderCreatorMock->expects(static::once())->method('createPaymentOrder')
      ->with($drawdown);
    $this->subscriber->onAccepted(new DrawdownAcceptedEvent($drawdown));
  }

}
