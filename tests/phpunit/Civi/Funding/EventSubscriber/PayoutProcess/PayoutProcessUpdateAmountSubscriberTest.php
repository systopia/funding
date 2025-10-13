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

namespace Civi\Funding\EventSubscriber\PayoutProcess;

use Civi\Funding\EntityFactory\PayoutProcessBundleFactory;
use Civi\Funding\Event\FundingCase\FundingCaseAmountApprovedUpdatedEvent;
use Civi\Funding\PayoutProcess\PayoutProcessManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\EventSubscriber\PayoutProcess\PayoutProcessUpdateAmountSubscriber
 */
final class PayoutProcessUpdateAmountSubscriberTest extends TestCase {

  /**
   * @var \Civi\Funding\PayoutProcess\PayoutProcessManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $payoutProcessManagerMock;

  private PayoutProcessUpdateAmountSubscriber $subscriber;

  protected function setUp(): void {
    parent::setUp();
    $this->payoutProcessManagerMock = $this->createMock(PayoutProcessManager::class);
    $this->subscriber = new PayoutProcessUpdateAmountSubscriber($this->payoutProcessManagerMock);
  }

  public function testGetSubscribedEvents(): void {
    $expectedSubscriptions = [
      FundingCaseAmountApprovedUpdatedEvent::class => 'onAmountApprovedUpdated',
    ];

    static::assertEquals($expectedSubscriptions, $this->subscriber::getSubscribedEvents());

    foreach ($expectedSubscriptions as $method) {
      static::assertTrue(method_exists($this->subscriber, $method));
    }
  }

  public function testOnAmountApprovedUpdated(): void {
    $payoutProcessBundle = PayoutProcessBundleFactory::create();
    $this->payoutProcessManagerMock->method('getLastBundleByFundingCaseId')
      ->willReturn($payoutProcessBundle);

    $this->payoutProcessManagerMock->expects(static::once())->method('updateAmountTotal')
      ->with($payoutProcessBundle, 1.23);

    $payoutProcessBundle->getFundingCase()->setAmountApproved(1.23);
    $event = new FundingCaseAmountApprovedUpdatedEvent(
      $payoutProcessBundle->getFundingCase(),
      $payoutProcessBundle->getFundingCaseType(),
      $payoutProcessBundle->getFundingProgram()
    );
    $this->subscriber->onAmountApprovedUpdated($event);
  }

}
