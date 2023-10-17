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

namespace Civi\Funding\ApplicationProcess;

use Civi\Api4\FundingApplicationSnapshot;
use Civi\Funding\Entity\ApplicationSnapshotEntity;
use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\Api4\Query\Comparison;

class ApplicationSnapshotManager {

  private Api4Interface $api4;

  public function __construct(Api4Interface $api4) {
    $this->api4 = $api4;
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function add(ApplicationSnapshotEntity $applicationSnapshot): void {
    $result = $this->api4->createEntity(
      FundingApplicationSnapshot::getEntityName(),
      $applicationSnapshot->toArray(),
      ['checkPermissions' => FALSE],
    );
    // @phpstan-ignore-next-line
    $applicationSnapshot->setValues($result->first());
  }

  /**
   * @phpstan-return array<ApplicationSnapshotEntity>
   *
   * @throws \CRM_Core_Exception
   */
  public function getByApplicationProcessId(int $applicationProcessId): array {
    $result = $this->api4->getEntities(FundingApplicationSnapshot::getEntityName(),
      Comparison::new('application_process_id', '=', $applicationProcessId),
      ['id' => 'DESC']
    );

    return ApplicationSnapshotEntity::allFromApiResult($result);
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function getLastByApplicationProcessId(int $applicationProcessId): ?ApplicationSnapshotEntity {
    $result = $this->api4->getEntities(
      FundingApplicationSnapshot::getEntityName(),
      Comparison::new('application_process_id', '=', $applicationProcessId),
      ['id' => 'DESC'],
      1,
      0,
      ['checkPermissions' => FALSE],
    );

    // @phpstan-ignore-next-line
    return 0 === $result->count() ? NULL : ApplicationSnapshotEntity::fromArray($result->first());
  }

}
