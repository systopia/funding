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

use Civi\Api4\FundingRecipientContactRelation;
use Civi\Funding\Contact\Relation\Types\Relationship;
use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\Api4\Query\Comparison;

final class Upgrader0012 implements UpgraderInterface {

  private Api4Interface $api4;

  public function __construct(Api4Interface $api4) {
    $this->api4 = $api4;
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function execute(\Log $log): void {
    $log->info('Migrate recipient contact relations');
    $this->migrateRelationshipTypeRelations();
    $this->migrateContactTypeAndRelationshipTypeRelations();
  }

  /**
   * @throws \CRM_Core_Exception
   */
  private function migrateRelationshipTypeRelations(): void {
    /** @phpstan-var list<array{id: int, type: string, properties: array{relationshipTypeId: int, contactTypeId: int}}> $relations */
    $relations = $this->api4->getEntities(
      FundingRecipientContactRelation::getEntityName(),
      Comparison::new('type', '=', 'RelationshipType')
    );

    foreach ($relations as $relation) {
      $this->api4->updateEntity(FundingRecipientContactRelation::getEntityName(), $relation['id'], [
        'type' => Relationship::NAME,
        'properties' => [
          'relationshipTypeIds' => [$relation['properties']['relationshipTypeId']],
          'contactTypeIds' => [],
          'groupIds' => [],
        ],
      ]);
    }
  }

  /**
   * @throws \CRM_Core_Exception
   */
  private function migrateContactTypeAndRelationshipTypeRelations(): void {
    /** @phpstan-var list<array{id: int, type: string, properties: array{relationshipTypeId: int, contactTypeId: int}}> $relations */
    $relations = $this->api4->getEntities(
      FundingRecipientContactRelation::getEntityName(),
      Comparison::new('type', '=', 'ContactTypeAndRelationshipType')
    );

    foreach ($relations as $relation) {
      $this->api4->updateEntity(FundingRecipientContactRelation::getEntityName(), $relation['id'], [
        'type' => Relationship::NAME,
        'properties' => [
          'relationshipTypeIds' => [$relation['properties']['relationshipTypeId']],
          'contactTypeIds' => [$relation['properties']['contactTypeId']],
          'groupIds' => [],
        ],
      ]);
    }
  }

}
