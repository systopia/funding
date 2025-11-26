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
use Civi\Funding\EntityFactory\FundingCaseBundleFactory;
use Civi\Funding\EntityFactory\PayoutProcessFactory;
use Civi\Funding\PayoutProcess\PayoutProcessManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\PayoutProcess\Task\AbstractDrawdownCreateTaskHandler
 */
final class AbstractDrawdownCreateTaskHandlerTest extends TestCase {

  private MockObject&PayoutProcessManager $payoutProcessManagerMock;

  private AbstractDrawdownCreateTaskHandler $taskHandler;

  protected function setUp(): void {
    parent::setUp();
    $this->payoutProcessManagerMock = $this->createMock(PayoutProcessManager::class);
    $this->taskHandler = new class ($this->payoutProcessManagerMock) extends AbstractDrawdownCreateTaskHandler {

      public static function getSupportedFundingCaseTypes(): array {
        return [];
      }

    };
  }

  public function testCreateTasksOnChanged(): void {
    $fundingCaseBundle = FundingCaseBundleFactory::create(['status' => 'ongoing', 'amount_approved' => 1.1]);
    $fundingCase = $fundingCaseBundle->getFundingCase();
    $previousFundingCase = clone $fundingCase;
    $previousFundingCase->setAmountApproved(1.0);
    $payoutProcess = PayoutProcessFactory::create();

    $this->payoutProcessManagerMock->expects(static::once())->method('getLastByFundingCaseId')
      ->with($fundingCase->getId())
      ->willReturn($payoutProcess);

    $this->payoutProcessManagerMock->expects(static::once())->method('getAmountAvailable')
      ->with($payoutProcess)
      ->willReturn(0.1);

    static::assertEquals([
      FundingTaskEntity::newTask([
        'subject' => 'Create Drawdown',
        'affected_identifier' => $fundingCase->getIdentifier(),
        'required_permissions' => ['drawdown_create'],
        'type' => 'drawdown_create',
        'funding_case_id' => $fundingCase->getId(),
        'payout_process_id' => $payoutProcess->getId(),
      ]),
    ], [...$this->taskHandler->createTasksOnChange($fundingCaseBundle, $previousFundingCase)]);
  }

  public function testCreateTasksOnChangedNotOngoing(): void {
    $fundingCaseBundle = FundingCaseBundleFactory::create(['status' => 'some_status', 'amount_approved' => 1.1]);
    $fundingCase = $fundingCaseBundle->getFundingCase();
    $previousFundingCase = clone $fundingCase;
    $previousFundingCase->setAmountApproved(1.0);

    $this->payoutProcessManagerMock->expects(static::never())->method('getLastByFundingCaseId');

    static::assertSame([], [...$this->taskHandler->createTasksOnChange($fundingCaseBundle, $previousFundingCase)]);
  }

  public function testCreateTasksOnNew(): void {
    $fundingCaseBundle = FundingCaseBundleFactory::create();
    static::assertSame([], [...$this->taskHandler->createTasksOnNew($fundingCaseBundle)]);
  }

  public function testModifyTaskOnAmountAvailableNotZero(): void {
    $fundingCaseBundle = FundingCaseBundleFactory::create(['status' => 'ongoing', 'amount_approved' => 1.1]);
    $fundingCase = $fundingCaseBundle->getFundingCase();
    $previousFundingCase = clone $fundingCase;
    $payoutProcess = PayoutProcessFactory::create();

    $task = FundingTaskEntity::newTask([
      'subject' => 'Create Drawdown',
      'affected_identifier' => $fundingCase->getIdentifier(),
      'required_permissions' => ['drawdown_create'],
      'type' => 'drawdown_create',
      'funding_case_id' => $fundingCase->getId(),
    ]);

    $this->payoutProcessManagerMock->expects(static::once())->method('getLastByFundingCaseId')
      ->with($fundingCase->getId())
      ->willReturn($payoutProcess);
    $this->payoutProcessManagerMock->expects(static::once())->method('getAmountAvailable')
      ->with($payoutProcess)
      ->willReturn(0.1);

    static::assertFalse($this->taskHandler->modifyTask($task, $fundingCaseBundle, $previousFundingCase));
    static::assertSame('Scheduled', $task->getStatusName());
  }

  public function testModifyTaskOnAmountAvailableZero(): void {
    $fundingCaseBundle = FundingCaseBundleFactory::create(['status' => 'ongoing', 'amount_approved' => 1.1]);
    $fundingCase = $fundingCaseBundle->getFundingCase();
    $previousFundingCase = clone $fundingCase;
    $payoutProcess = PayoutProcessFactory::create();

    $task = FundingTaskEntity::newTask([
      'subject' => 'Create Drawdown',
      'affected_identifier' => $fundingCase->getIdentifier(),
      'required_permissions' => ['drawdown_create'],
      'type' => 'drawdown_create',
      'funding_case_id' => $fundingCase->getId(),
    ]);

    $this->payoutProcessManagerMock->expects(static::once())->method('getLastByFundingCaseId')
      ->with($fundingCase->getId())
      ->willReturn($payoutProcess);
    $this->payoutProcessManagerMock->expects(static::once())->method('getAmountAvailable')
      ->with($payoutProcess)
      ->willReturn(0.0);

    static::assertTrue($this->taskHandler->modifyTask($task, $fundingCaseBundle, $previousFundingCase));
    static::assertSame('Completed', $task->getStatusName());
  }

  public function testModifyTaskOnClosed(): void {
    $fundingCaseBundle = FundingCaseBundleFactory::create(['status' => 'closed', 'amount_approved' => 1.1]);
    $fundingCase = $fundingCaseBundle->getFundingCase();
    $previousFundingCase = clone $fundingCase;
    $previousFundingCase->setStatus('ongoing');

    $task = FundingTaskEntity::newTask([
      'subject' => 'Create Drawdown',
      'affected_identifier' => $fundingCase->getIdentifier(),
      'required_permissions' => ['drawdown_create'],
      'type' => 'drawdown_create',
      'funding_case_id' => $fundingCase->getId(),
    ]);

    $this->payoutProcessManagerMock->expects(static::never())->method('getLastByFundingCaseId');

    static::assertTrue($this->taskHandler->modifyTask($task, $fundingCaseBundle, $previousFundingCase));
    static::assertSame('Completed', $task->getStatusName());
  }

}
