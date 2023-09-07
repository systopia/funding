<?php
/*
 * Copyright (C) 2022 SYSTOPIA GmbH
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
use Civi\Funding\Permission\ContactRelation\Types\ContactRelationship;
use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\Api4\Query\Comparison;
use Civi\RemoteTools\Api4\Query\CompositeCondition;
use Webmozart\Assert\Assert;

/**
 * Checks if a contact has a relationship of a given type to a given contact.
 */
final class ContactRelationshipChecker implements ContactRelationCheckerInterface {

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
    $relationshipTypeId = $relationProperties['relationshipTypeId'];
    Assert::numeric($relationshipTypeId);
    $relatedContactId = $relationProperties['contactId'];
    Assert::numeric($relatedContactId);

    $action = Relationship::get(FALSE)
      ->addSelect('id')
      ->addWhere('relationship_type_id', '=', $relationshipTypeId)
      ->addWhere('is_active', '=', TRUE)
      ->addClause('OR',
        CompositeCondition::new('AND',
          Comparison::new('contact_id_a', '=', $contactId),
          Comparison::new('contact_id_b', '=', $relatedContactId),
        )->toArray(),
        CompositeCondition::new('AND',
          Comparison::new('contact_id_a', '=', $relatedContactId),
          Comparison::new('contact_id_b', '=', $contactId),
        )->toArray(),
      );

    return $this->api4->executeAction($action)->rowCount >= 1;
  }

  public function supportsRelationType(string $relationType): bool {
    return ContactRelationship::NAME === $relationType;
  }

}
