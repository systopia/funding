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

namespace Civi\Funding\PayoutProcess;

use Civi\Api4\Generic\Result;
use Civi\Api4\PayoutProcess;
use Civi\Core\CiviEventDispatcherInterface;
use Civi\Funding\EntityFactory\FundingCaseFactory;
use Civi\Funding\EntityFactory\PayoutProcessFactory;
use Civi\Funding\Event\PayoutProcess\PayoutProcessCreatedEvent;
use Civi\RemoteTools\Api4\Api4Interface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\PayoutProcess\PayoutProcessManager
 */
final class PayoutProcessManagerTest extends TestCase {

  /**
   * @var \Civi\RemoteTools\Api4\Api4Interface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $api4Mock;

  /**
   * @var \Civi\Core\CiviEventDispatcherInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $eventDispatcherMock;

  private PayoutProcessManager $payoutProcessManager;

  protected function setUp(): void {
    parent::setUp();
    $this->api4Mock = $this->createMock(Api4Interface::class);
    $this->eventDispatcherMock = $this->createMock(CiviEventDispatcherInterface::class);
    $this->payoutProcessManager = new PayoutProcessManager(
      $this->api4Mock,
      $this->eventDispatcherMock,
    );
  }

  public function testCreate(): void {
    $fundingCase = FundingCaseFactory::createFundingCase(['amount_approved' => 12.34]);
    $payoutProcess = PayoutProcessFactory::create(['amount_total' => 12.34]);

    $this->api4Mock->expects(static::once())->method('createEntity')
      ->with(PayoutProcess::_getEntityName(), [
        'funding_case_id' => FundingCaseFactory::DEFAULT_ID,
        'status' => 'open',
        'amount_total' => 12.34,
        'amount_paid_out' => 0.0,
      ])
      ->willReturn(new Result([$payoutProcess->toArray()]));

    $this->eventDispatcherMock->expects(static::once())->method('dispatch')
      ->with(PayoutProcessCreatedEvent::class, new PayoutProcessCreatedEvent($fundingCase, $payoutProcess));

    static::assertEquals($payoutProcess, $this->payoutProcessManager->create($fundingCase, 12.34));
  }

}
