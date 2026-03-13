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

use Civi\Funding\Entity\ApplicationProcessEntity;
use Civi\Funding\Entity\ApplicationSnapshotEntity;
use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\Api4\Query\Comparison;

final class Upgrader0020 implements UpgraderInterface {

  private Api4Interface $api4;

  public function __construct(Api4Interface $api4) {
    $this->api4 = $api4;
  }

  public function execute(\Log $log): void {
    $log->info('Set amount_eligible of application processes and application snapshots.');
    $this->setAmountsEligible();
  }

  /**
   * @throws \CRM_Core_Exception
   */
  private function setAmountsEligible(): void {
    // amount_eligible was set to amount_requested for eligible processes and
    // snapshots in 0020.sql.

    $snapshotsByProcessId = $this->getSnapshotsByProcessId();
    // Set amount eligible of snapshots with undecided eligibility to the amount
    // eligible of the previous snapshot (if any).
    foreach ($snapshotsByProcessId as $snapshots) {
      foreach ($snapshots as $i => $snapshot) {
        if (NULL === $snapshot->getIsEligible() && 0 !== $i) {
          $previousAmountEligible = $snapshots[$i - 1]->getAmountEligible();
          if ($previousAmountEligible !== 0.0) {
            $snapshot->setValues(['amount_eligible' => $previousAmountEligible] + $snapshot->toArray());
            $this->api4->updateEntity(
              'FundingApplicationSnapshot',
              $snapshot->getId(),
              ['amount_eligible' => $snapshot->getAmountEligible()]
            );
          }
        }
      }
    }

    // Set amount eligible of application processes with undecided eligibility
    // to the amount eligible of the last snapshot (if any).
    foreach ($this->getProcessesWithUndecidedEligibility() as $process) {
      if (isset($snapshotsByProcessId[$process->getId()])) {
        $lastSnapshot = end($snapshotsByProcessId[$process->getId()]);
        if (0.0 !== $lastSnapshot->getAmountEligible()) {
          $this->api4->updateEntity(
            'FundingApplicationProcess',
            $process->getId(),
            ['amount_eligible' => $lastSnapshot->getAmountEligible()]
          );
        }
      }
    }
  }

  /**
   * @phpstan-return array<int, non-empty-list<ApplicationSnapshotEntity>>
   *
   * @throws \CRM_Core_Exception
   */
  private function getSnapshotsByProcessId(): array {
    $result = $this->api4->getEntities(
      'FundingApplicationSnapshot',
      orderBy: ['id' => 'ASC']
    );

    $snapshots = [];
    foreach (ApplicationSnapshotEntity::allFromApiResult($result) as $snapshot) {
      $snapshots[$snapshot->getApplicationProcessId()][] = $snapshot;
    }

    return $snapshots;
  }

  /**
   * @return list<ApplicationProcessEntity>
   *
   * @throws \CRM_Core_Exception
   */
  private function getProcessesWithUndecidedEligibility(): array {
    // @phpstan-ignore return.type
    return ApplicationProcessEntity::allFromApiResult($this->api4->getEntities(
      'FundingApplicationProcess',
      Comparison::new('is_eligible', '=', NULL)
    ));
  }

}
