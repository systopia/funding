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

use Civi\Funding\EntityFactory\FundingCaseBundleFactory;
use Civi\Funding\EntityFactory\PayoutProcessFactory;
use Civi\Funding\Event\FundingCase\FundingCaseApprovedEvent;
use Civi\Funding\PayoutProcess\PayoutProcessManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\EventSubscriber\PayoutProcess\PayoutProcessCreateSubscriber
 */
final class PayoutProcessCreateSubscriberTest extends TestCase {

  private PayoutProcessManager&MockObject $payoutProcessManagerMock;

  private PayoutProcessCreateSubscriber $subscriber;

  protected function setUp(): void {
    parent::setUp();
    $this->payoutProcessManagerMock = $this->createMock(PayoutProcessManager::class);
    $this->subscriber = new PayoutProcessCreateSubscriber($this->payoutProcessManagerMock);
  }

  public function testGetSubscribedEvents(): void {
    $expectedSubscriptions = [
      FundingCaseApprovedEvent::class => 'onApproved',
    ];

    static::assertEquals($expectedSubscriptions, $this->subscriber::getSubscribedEvents());

    foreach ($expectedSubscriptions as $method) {
      static::assertTrue(method_exists($this->subscriber, $method));
    }
  }

  public function testOnApproved(): void {
    $fundingCaseBundle = FundingCaseBundleFactory::create();

    $event = new FundingCaseApprovedEvent($fundingCaseBundle, 12.34);

    $this->payoutProcessManagerMock->expects(static::once())->method('create')
      ->with($fundingCaseBundle->getFundingCase(), 12.34)
      ->willReturn(PayoutProcessFactory::create());
    $this->subscriber->onApproved($event);
  }

}
