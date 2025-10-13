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

namespace Civi\Funding\PayoutProcess\Task;

use Civi\Funding\Entity\FundingTaskEntity;
use Civi\Funding\EntityFactory\DrawdownBundleFactory;
use Civi\Funding\EntityFactory\DrawdownFactory;
use Civi\Funding\PayoutProcess\PayoutProcessManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\PayoutProcess\Task\AbstractDrawdownCreateTaskCreator
 */
final class AbstractDrawdownCreateTaskCreatorTest extends TestCase {

  private MockObject&PayoutProcessManager $payoutProcessManagerMock;

  private AbstractDrawdownCreateTaskCreator $taskCreator;

  protected function setUp(): void {
    parent::setUp();
    $this->payoutProcessManagerMock = $this->createMock(PayoutProcessManager::class);
    $this->taskCreator = new class  ($this->payoutProcessManagerMock) extends AbstractDrawdownCreateTaskCreator {

      public static function getSupportedFundingCaseTypes(): array {
        return [];
      }

    };
  }

  public function testCreateTasksOnChange(): void {
    $drawdownBundle = DrawdownBundleFactory::create();
    $previousDrawdown = DrawdownFactory::create();

    static::assertSame([], [...$this->taskCreator->createTasksOnChange($drawdownBundle, $previousDrawdown)]);
  }

  public function testCreateTasksOnDelete(): void {
    $drawdownBundle = DrawdownBundleFactory::create();
    $this->payoutProcessManagerMock->expects(static::once())->method('getAmountAvailable')
      ->with($drawdownBundle->getPayoutProcess())
      ->willReturn(0.1);

    static::assertEquals([
      FundingTaskEntity::newTask([
        'subject' => 'Create Drawdown',
        'affected_identifier' => $drawdownBundle->getFundingCase()->getIdentifier(),
        'required_permissions' => ['drawdown_create'],
        'type' => 'drawdown_create',
        'funding_case_id' => $drawdownBundle->getFundingCase()->getId(),
        'payout_process_id' => $drawdownBundle->getPayoutProcess()->getId(),
      ]),
    ], [...$this->taskCreator->createTasksOnDelete($drawdownBundle)]);
  }

  public function testCreateTasksOnDeleteAmountAvailableZero(): void {
    $drawdownBundle = DrawdownBundleFactory::create();
    $this->payoutProcessManagerMock->expects(static::once())->method('getAmountAvailable')
      ->with($drawdownBundle->getPayoutProcess())
      ->willReturn(0.0);

    static::assertSame([], [...$this->taskCreator->createTasksOnDelete($drawdownBundle)]);
  }

  public function testCreateTasksOnNew(): void {
    $drawdownBundle = DrawdownBundleFactory::create();
    static::assertSame([], [...$this->taskCreator->createTasksOnNew($drawdownBundle)]);
  }

}
