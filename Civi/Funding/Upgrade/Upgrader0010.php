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

namespace Civi\Funding\Upgrade;

use Civi\Api4\FundingApplicationProcess;
use Civi\Api4\FundingApplicationSnapshot;
use Civi\Funding\ApplicationProcess\ActionStatusInfo\ApplicationProcessActionStatusInfoContainer;
use Civi\RemoteTools\Api4\Api4Interface;

final class Upgrader0010 implements UpgraderInterface {

  private Api4Interface $api4;

  private ApplicationProcessActionStatusInfoContainer $infoContainer;

  public function __construct(Api4Interface $api4, ApplicationProcessActionStatusInfoContainer $infoContainer) {
    $this->api4 = $api4;
    $this->infoContainer = $infoContainer;
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function execute(\Log $log): void {
    $log->info('Set application process status flags');
    $applicationProcesses = $this->api4->execute(FundingApplicationProcess::getEntityName(), 'get', [
      'select' => ['id', 'status', 'funding_case_id.funding_case_type_id.name'],
    ]);

    /** @phpstan-var array{
     *   id: int,
     *   status: string,
     *   'funding_case_id.funding_case_type_id.name': string,
     * } $applicationProcess
    */
    foreach ($applicationProcesses as $applicationProcess) {
      $this->api4->updateEntity(
        FundingApplicationProcess::getEntityName(),
        $applicationProcess['id'],
        $this->buildStatusMap(
          $applicationProcess['status'],
          $applicationProcess['funding_case_id.funding_case_type_id.name'])
      );
    }

    $log->info('Set application snapshot status flags');
    $applicationSnapshots = $this->api4->execute(FundingApplicationSnapshot::getEntityName(), 'get', [
      'select' => ['id', 'status', 'application_process_id.funding_case_id.funding_case_type_id.name'],
    ]);

    /** @phpstan-var array{
     *   id: int,
     *   status: string,
     *   'application_process_id.funding_case_id.funding_case_type_id.name': string,
     * } $applicationSnapshot
    */
    foreach ($applicationSnapshots as $applicationSnapshot) {
      $this->api4->updateEntity(
        FundingApplicationSnapshot::getEntityName(),
        $applicationSnapshot['id'],
        $this->buildStatusMap(
          $applicationSnapshot['status'],
          $applicationSnapshot['application_process_id.funding_case_id.funding_case_type_id.name'])
      );
    }
  }

  /**
   * @phpstan-return array<string, mixed>
   */
  private function buildStatusMap(string $status, string $fundingCaseType): array {
    $info = $this->infoContainer->get($fundingCaseType);
    return [
      'is_in_work' => $info->isInWorkStatus($status),
      'is_rejected' => $info->isRejectedStatus($status),
      'is_withdrawn' => $info->isWithdrawnStatus($status),
    ];
  }

}
