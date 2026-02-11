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

namespace Civi\Funding\Permission\ContactRelation\Checker;

use Civi\Api4\Relationship;
use Civi\Funding\Permission\ContactRelation\ContactRelationCheckerInterface;
use Civi\Funding\Permission\ContactRelation\Types\ContactRelationships;
use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\Api4\Query\Comparison;
use Civi\RemoteTools\Api4\Query\CompositeCondition;
use Webmozart\Assert\Assert;

/**
 * Checks if a contact has all given relationships specified by relationship
 * type and contact ID.
 */
final class ContactRelationshipsChecker implements ContactRelationCheckerInterface {

  private Api4Interface $api4;

  public function __construct(Api4Interface $api4) {
    $this->api4 = $api4;
  }

  /**
   * @inheritDoc
   *
   * @throws \CRM_Core_Exception
   */
  public function hasRelation(int $contactId, string $relationType, array $relationProperties): bool {
    $relationships = $relationProperties['relationships'];
    Assert::isArray($relationships);
    Assert::notEmpty($relationships);

    $conditions = [];
    foreach ($relationships as $relationship) {
      Assert::isArray($relationship);
      Assert::integerish($relationship['contactId']);
      Assert::integerish($relationship['relationshipTypeId']);

      $conditions[] = CompositeCondition::new('AND',
        Comparison::new('relationship_type_id', '=', $relationship['relationshipTypeId']),
        Comparison::new('is_active', '=', TRUE),
        CompositeCondition::new('OR',
          CompositeCondition::new('AND',
            Comparison::new('contact_id_a', '=', $contactId),
            Comparison::new('contact_id_b', '=', $relationship['contactId']),
          ),
          CompositeCondition::new('AND',
            Comparison::new('contact_id_a', '=', $relationship['contactId']),
            Comparison::new('contact_id_b', '=', $contactId),
          )
        )
      );
    }

    // CiviCRM prevents duplicate relations, i.e. relations of same type with
    // contact_id_a and contact_id_b swapped. So we can use this count.
    return $this->api4->countEntities(Relationship::getEntityName(), CompositeCondition::new('OR', ...$conditions))
      === count($relationships);
  }

  public function supportsRelationType(string $relationType): bool {
    return ContactRelationships::NAME === $relationType;
  }

}
