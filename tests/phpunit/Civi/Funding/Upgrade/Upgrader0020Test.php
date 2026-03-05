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

namespace Civi\Funding\Upgrade;

use Civi\Api4\FundingApplicationProcess;
use Civi\Api4\FundingApplicationSnapshot;
use Civi\Funding\Fixtures\ApplicationProcessBundleFixture;
use Civi\Funding\Fixtures\ApplicationProcessFixture;
use Civi\Funding\Fixtures\ApplicationSnapshotFixture;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\Upgrade\Upgrader0020
 */
final class Upgrader0020Test extends TestCase {

  public function testExecute(): void {
    $eligibleProcessBundle = ApplicationProcessBundleFixture::create([
      'is_eligible' => TRUE,
      'amount_requested' => 100.0,
      'amount_eligible' => 100.0,
    ]);
    $eligibleProcess = $eligibleProcessBundle->getApplicationProcess();

    $fundingCaseId = $eligibleProcessBundle->getFundingCase()->getId();

    $ineligibleProcess = ApplicationProcessFixture::addFixture($fundingCaseId, [
      'is_eligible' => FALSE,
      'amount_requested' => 200.0,
      'amount_eligible' => 0.0,
    ]);

    $undecidedProcess = ApplicationProcessFixture::addFixture($fundingCaseId, [
      'is_eligible' => NULL,
      'amount_requested' => 300.0,
      'amount_eligible' => 0.0,
    ]);

    $undecidedProcessWithSnapshot = ApplicationProcessFixture::addFixture($fundingCaseId, [
      'is_eligible' => NULL,
      'amount_requested' => 400.0,
      'amount_eligible' => 0.0,
    ]);
    $undecidedProcessWithSnapshotId = $undecidedProcessWithSnapshot->getId();
    $undecidedSnapshot1 = ApplicationSnapshotFixture::addFixture($undecidedProcessWithSnapshotId, [
      'is_eligible' => NULL,
      'amount_requested' => 490.0,
      'amount_eligible' => 0.0,
    ]);
    $eligibleSnapshot1 = ApplicationSnapshotFixture::addFixture($undecidedProcessWithSnapshotId, [
      'is_eligible' => TRUE,
      'amount_requested' => 480.0,
      'amount_eligible' => 480.0,
    ]);
    $undecidedSnapshot2 = ApplicationSnapshotFixture::addFixture($undecidedProcessWithSnapshotId, [
      'is_eligible' => NULL,
      'amount_requested' => 480.0,
      'amount_eligible' => 0.0,
    ]);
    $ineligibleSnapshot = ApplicationSnapshotFixture::addFixture($undecidedProcessWithSnapshotId, [
      'is_eligible' => FALSE,
      'amount_requested' => 490.0,
      'amount_eligible' => 0.0,
    ]);
    $eligibleSnapshot2 = ApplicationSnapshotFixture::addFixture($undecidedProcessWithSnapshotId, [
      'is_eligible' => TRUE,
      'amount_requested' => 470.0,
      'amount_eligible' => 470.0,
    ]);

    /** @var \Civi\Funding\Upgrade\Upgrader0020 $upgrader */
    $upgrader = \Civi::service(Upgrader0020::class);
    $upgrader->execute(new \Log_null('test'));

    // Should not be changed.
    static::assertSame(100.0, $this->getProcessAmountEligible($eligibleProcess->getId()));
    // Should not be changed.
    static::assertSame(0.0, $this->getProcessAmountEligible($ineligibleProcess->getId()));
    // Should not be changed.
    static::assertSame(0.0, $this->getProcessAmountEligible($undecidedProcess->getId()));

    // Should be set to value of last snapshot.
    static::assertSame(470.0, $this->getProcessAmountEligible($undecidedProcessWithSnapshotId));
    // Should not be changed.
    static::assertSame(0.0, $this->getSnapshotAmountEligible($undecidedSnapshot1->getId()));
    // Should not be changed.
    static::assertSame(480.0, $this->getSnapshotAmountEligible($eligibleSnapshot1->getId()));
    // Should be set to value of previous snapshot.
    static::assertSame(480.0, $this->getSnapshotAmountEligible($undecidedSnapshot2->getId()));
    // Should not be changed.
    static::assertSame(0.0, $this->getSnapshotAmountEligible($ineligibleSnapshot->getId()));
    // Should not be changed.
    static::assertSame(470.0, $this->getSnapshotAmountEligible($eligibleSnapshot2->getId()));
  }

  private function getProcessAmountEligible(int $applicationProcessId): float {
    return FundingApplicationProcess::get(FALSE)
      ->addSelect('amount_eligible')
      ->addWhere('id', '=', $applicationProcessId)
      ->execute()
      ->single()['amount_eligible'];
  }

  private function getSnapshotAmountEligible(int $applicationProcessId): float {
    return FundingApplicationSnapshot::get(FALSE)
      ->addSelect('amount_eligible')
      ->addWhere('id', '=', $applicationProcessId)
      ->execute()
      ->single()['amount_eligible'];
  }

}
