<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
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

use Civi\RemoteTools\Api4\Api4Interface;
use Civi\Api4\FundingClearingProcess;

final class Upgrader0014 implements UpgraderInterface {

  private Api4Interface $api4;

  public function __construct(Api4Interface $api4) {
    $this->api4 = $api4;
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function execute(\Log $log): void {
    $log->info('Set ClearingProcess start and end date');

    $clearingProcesses = $this->api4->execute(FundingClearingProcess::getEntityName(), 'get', [
      'select' => [
        'id',
        'report_data',
      ],
      'where' => [
        ['status', '!=', 'not-started'],
      ],
    ]);

    /** @phpstan-var array{id: int, report_data: array<string, mixed>} $clearingProcess */
    foreach ($clearingProcesses as $clearingProcess) {
      /** @phpstan-var list<array{beginn: string, ende: string}> $dateRanges */
      // @phpstan-ignore offsetAccess.nonOffsetAccessible
      $dateRanges = $clearingProcess['report_data']['grunddaten']['zeitraeume'] ?? [];
      $startDate = $dateRanges[0]['beginn'] ?? NULL;
      $endDate = end($dateRanges)['ende'] ?? NULL;

      if (NULL !== $startDate || NULL !== $endDate) {
        $this->api4->updateEntity(FundingClearingProcess::getEntityName(), $clearingProcess['id'], [
          'start_date' => $startDate,
          'end_date' => $endDate,
        ]);
      }
    }
  }

}
