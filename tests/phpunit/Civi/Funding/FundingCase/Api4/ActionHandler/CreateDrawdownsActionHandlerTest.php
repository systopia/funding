<?php
/*
 * Copyright (C) 2026 SYSTOPIA GmbH
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

namespace Civi\Funding\FundingCase\Api4\ActionHandler;

use Civi\API\Exception\UnauthorizedException;
use Civi\Funding\Api4\Action\FundingCase\CreateDrawdownsAction;
use Civi\Funding\EntityFactory\DrawdownFactory;
use Civi\Funding\EntityFactory\PayoutProcessBundleFactory;
use Civi\Funding\FundingCase\FundingCasePermissions;
use Civi\Funding\Mock\RequestContext\TestRequestContext;
use Civi\Funding\PayoutProcess\DrawdownManager;
use Civi\Funding\PayoutProcess\PayoutProcessManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\FundingCase\Api4\ActionHandler\CreateDrawdownsActionHandler
 */
final class CreateDrawdownsActionHandlerTest extends TestCase {

  private DrawdownManager&MockObject $drawdownManagerMock;

  private CreateDrawdownsActionHandler $handler;

  private MockObject&PayoutProcessManager $payoutProcessManagerMock;

  protected function setUp(): void {
    parent::setUp();
    $this->drawdownManagerMock = $this->createMock(DrawdownManager::class);
    $this->payoutProcessManagerMock = $this->createMock(PayoutProcessManager::class);
    $this->handler = new CreateDrawdownsActionHandler(
      $this->drawdownManagerMock,
      $this->payoutProcessManagerMock,
      new TestRequestContext(1234, FALSE)
    );
  }

  public function test(): void {
    $action = (new CreateDrawdownsAction())
      ->setAmountPercent(10)
      ->setIds([2]);

    $payoutProcessBundle = PayoutProcessBundleFactory::create(
      ['amount_total' => 12.34],
      ['permissions' => [FundingCasePermissions::REVIEW_DRAWDOWN_CREATE]],
    );

    $this->payoutProcessManagerMock->method('getLastBundleByFundingCaseId')
      ->with(2)
      ->willReturn($payoutProcessBundle);

    $this->payoutProcessManagerMock->method('getAmountAvailable')
      ->with($payoutProcessBundle->getPayoutProcess())
      ->willReturn(1.24);

    $drawdown = DrawdownFactory::create();
    $this->drawdownManagerMock->expects(static::once())->method('createNew')
      ->with($payoutProcessBundle, 1.23, 1234)
      ->willReturn($drawdown);

    static::assertSame(
      [2 => $drawdown->toArray()],
      $this->handler->createDrawdowns($action)
    );
  }

  public function testUsesAmountAvailable(): void {
    $action = (new CreateDrawdownsAction())
      ->setAmountPercent(10)
      ->setIds([2]);

    $payoutProcessBundle = PayoutProcessBundleFactory::create(
      ['amount_total' => 12.34],
      ['permissions' => [FundingCasePermissions::REVIEW_DRAWDOWN_CREATE]],
    );

    $this->payoutProcessManagerMock->method('getLastBundleByFundingCaseId')
      ->with(2)
      ->willReturn($payoutProcessBundle);

    $this->payoutProcessManagerMock->method('getAmountAvailable')
      ->with($payoutProcessBundle->getPayoutProcess())
      ->willReturn(1.22);

    $drawdown = DrawdownFactory::create();
    $this->drawdownManagerMock->expects(static::once())->method('createNew')
      ->with($payoutProcessBundle, 1.22, 1234)
      ->willReturn($drawdown);

    static::assertSame(
      [2 => $drawdown->toArray()],
      $this->handler->createDrawdowns($action)
    );
  }

  public function testNoAmountAvailable(): void {
    $action = (new CreateDrawdownsAction())
      ->setAmountPercent(10)
      ->setIds([2]);

    $payoutProcessBundle = PayoutProcessBundleFactory::create(
      ['amount_total' => 12.34],
      ['permissions' => [FundingCasePermissions::REVIEW_DRAWDOWN_CREATE]],
    );

    $this->payoutProcessManagerMock->method('getLastBundleByFundingCaseId')
      ->with(2)
      ->willReturn($payoutProcessBundle);

    $this->payoutProcessManagerMock->method('getAmountAvailable')
      ->with($payoutProcessBundle->getPayoutProcess())
      ->willReturn(0.0);

    $this->drawdownManagerMock->expects(static::never())->method('createNew');

    static::assertSame([], $this->handler->createDrawdowns($action));
  }

  public function testPermissionMissing(): void {
    $action = (new CreateDrawdownsAction())
      ->setAmountPercent(10)
      ->setIds([2]);

    $payoutProcessBundle = PayoutProcessBundleFactory::create(
      ['amount_total' => 12.34],
      ['permissions' => [FundingCasePermissions::REVIEW_FINISH]],
    );

    $this->payoutProcessManagerMock->method('getLastBundleByFundingCaseId')
      ->with(2)
      ->willReturn($payoutProcessBundle);

    $this->expectException(UnauthorizedException::class);
    $this->expectExceptionMessage('Drawdown creation for funding case ID 2 is not allowed.');

    $this->handler->createDrawdowns($action);
  }

}
