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

namespace Civi\Funding\Fixtures;

use Civi\Api4\FundingApplicationSnapshot;
use Civi\Funding\Entity\ApplicationSnapshotEntity;

final class ApplicationSnapshotFixture {

  /**
   * @phpstan-param array<string, mixed> $values
   */
  public static function addFixture(int $applicationProcessId, array $values = []): ApplicationSnapshotEntity {
    $result = FundingApplicationSnapshot::create(FALSE)
      ->setValues($values + [
        'application_process_id' => $applicationProcessId,
        'identifier' => 'test',
        'status' => 'approved',
        'title' => 'Title',
        'short_description' => 'Description',
        'request_data' => ['foo' => 'bar'],
        'amount_requested' => 1.2,
        'creation_date' => date('Y-m-d H:i:s'),
        'start_date' => NULL,
        'end_date' => NULL,
        'amount_granted' => NULL,
        'granted_budget' => NULL,
        'is_review_content' => TRUE,
        'is_review_calculative' => TRUE,
        'is_eligible' => NULL,
      ])->execute();

    return ApplicationSnapshotEntity::singleFromApiResult($result);
  }

}
