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

use Civi\Funding\EntityFactory\FundingCaseFactory;
use Civi\Funding\EntityFactory\FundingCaseTypeFactory;
use Civi\Funding\EntityFactory\FundingProgramFactory;
use Civi\Funding\EntityFactory\PayoutProcessFactory;
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
    $fundingCase = FundingCaseFactory::createFundingCase(['amount_approved' => 1.23]);
    $fundingCaseType = FundingCaseTypeFactory::createFundingCaseType();
    $fundingProgram = FundingProgramFactory::createFundingProgram();

    $payoutProcess = PayoutProcessFactory::create();
    $this->payoutProcessManagerMock->method('getLastByFundingCaseId')
      ->willReturn($payoutProcess);

    $this->payoutProcessManagerMock->expects(static::once())->method('updateAmountTotal')
      ->with($payoutProcess, 1.23);

    $event = new FundingCaseAmountApprovedUpdatedEvent($fundingCase, $fundingCaseType, $fundingProgram);
    $this->subscriber->onAmountApprovedUpdated($event);
  }

}
