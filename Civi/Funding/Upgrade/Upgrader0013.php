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

use Civi\Api4\FundingCaseContactRelation;
use Civi\Api4\FundingProgramContactRelation;
use Civi\Funding\EventSubscriber\FundingCase\FundingCasePermissionsCacheClearSubscriber;
use Civi\Funding\Permission\ContactRelation\Types\ContactRelationships;
use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\Api4\Query\Comparison;

final class Upgrader0013 implements UpgraderInterface {

  private Api4Interface $api4;

  public function __construct(Api4Interface $api4) {
    $this->api4 = $api4;
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function execute(\Log $log): void {
    $log->info('Migrate funding program permissions');
    $this->migrateRelations(FundingProgramContactRelation::getEntityName());

    $log->info('Migrate funding case permissions');
    FundingCasePermissionsCacheClearSubscriber::$fundingCaseContactRelationDisabled = TRUE;
    try {
      $this->migrateRelations(FundingCaseContactRelation::getEntityName());
    }
    finally {
      FundingCasePermissionsCacheClearSubscriber::$fundingCaseContactRelationDisabled = FALSE;
    }
  }

  /**
   * @throws \CRM_Core_Exception
   */
  private function migrateRelations(string $entityName): void {
    /** @phpstan-var list<array{id: int, type: string, properties: array{relationshipTypeId: int, contactId: int}}> $relations */
    $relations = $this->api4->getEntities(
      $entityName,
      Comparison::new('type', '=', 'ContactRelationship')
    );

    foreach ($relations as $relation) {
      $this->api4->updateEntity($entityName, $relation['id'], [
        'type' => ContactRelationships::NAME,
        'properties' => [
          'relationships' => [
            [
              'relationshipTypeId' => $relation['properties']['relationshipTypeId'],
              'contactId' => $relation['properties']['contactId'],
            ],
          ],
        ],
      ]);
    }
  }

}
