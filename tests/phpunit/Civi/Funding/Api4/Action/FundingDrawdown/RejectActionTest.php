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

namespace Civi\Funding\Api4\Action\FundingDrawdown;

use Civi\API\Exception\UnauthorizedException;
use Civi\Api4\Generic\Result;
use Civi\Funding\EntityFactory\DrawdownFactory;
use Civi\Funding\EntityFactory\FundingCaseFactory;
use Civi\Funding\EntityFactory\PayoutProcessFactory;
use Civi\Funding\FundingCase\FundingCaseManager;
use Civi\Funding\PayoutProcess\DrawdownManager;
use Civi\Funding\PayoutProcess\PayoutProcessManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\Api4\Action\FundingDrawdown\RejectAction
 */
final class RejectActionTest extends TestCase {

  /**
   * @var \Civi\Funding\Api4\Action\FundingDrawdown\RejectAction
   */
  private RejectAction $action;

  /**
   * @var \Civi\Funding\PayoutProcess\DrawdownManager|(\Civi\Funding\PayoutProcess\DrawdownManager&\PHPUnit\Framework\MockObject\MockObject)|\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $drawdownManagerMock;

  /**
   * @var \Civi\Funding\FundingCase\FundingCaseManager|(\Civi\Funding\FundingCase\FundingCaseManager&\PHPUnit\Framework\MockObject\MockObject)|\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $fundingCaseManagerMock;

  /**
   * @var \Civi\Funding\PayoutProcess\PayoutProcessManager|(\Civi\Funding\PayoutProcess\PayoutProcessManager&\PHPUnit\Framework\MockObject\MockObject)|\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $payoutProcessManagerMock;

  protected function setUp(): void {
    parent::setUp();
    $this->drawdownManagerMock = $this->createMock(DrawdownManager::class);
    $this->fundingCaseManagerMock = $this->createMock(FundingCaseManager::class);
    $this->payoutProcessManagerMock = $this->createMock(PayoutProcessManager::class);
    $this->action = new RejectAction(
      $this->drawdownManagerMock,
      $this->fundingCaseManagerMock,
      $this->payoutProcessManagerMock,
    );
  }

  public function testRun(): void {
    $drawdown = DrawdownFactory::create();
    $this->drawdownManagerMock->method('get')
      ->with($drawdown->getId())
      ->willReturn($drawdown);

    $payoutProcess = PayoutProcessFactory::create();
    $this->payoutProcessManagerMock->method('get')
      ->with($payoutProcess->getId())
      ->willReturn($payoutProcess);

    $fundingCase = FundingCaseFactory::createFundingCase(['permissions' => ['review_drawdown']]);
    $this->fundingCaseManagerMock->method('get')
      ->with($fundingCase->getId())
      ->willReturn($fundingCase);

    $this->drawdownManagerMock->expects(static::once())->method('delete')
      ->with($drawdown);

    $this->action->setId($drawdown->getId());
    $result = new Result();
    $this->action->_run($result);
    static::assertSame([$drawdown->toArray()], $result->getArrayCopy());
  }

  public function testRunWithoutPermission(): void {
    $drawdown = DrawdownFactory::create();
    $this->drawdownManagerMock->method('get')
      ->with($drawdown->getId())
      ->willReturn($drawdown);

    $payoutProcess = PayoutProcessFactory::create();
    $this->payoutProcessManagerMock->method('get')
      ->with($payoutProcess->getId())
      ->willReturn($payoutProcess);

    $fundingCase = FundingCaseFactory::createFundingCase(['permissions' => ['review_content']]);
    $this->fundingCaseManagerMock->method('get')
      ->with($fundingCase->getId())
      ->willReturn($fundingCase);

    $this->drawdownManagerMock->expects(static::never())->method('delete');

    $this->expectException(UnauthorizedException::class);
    $this->expectExceptionMessage('Permission to reject drawdown is missing.');

    $this->action->setId($drawdown->getId());
    $result = new Result();
    $this->action->_run($result);
  }

}
