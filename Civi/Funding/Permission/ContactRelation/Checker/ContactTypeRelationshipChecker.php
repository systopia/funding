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
use Civi\Funding\Permission\ContactRelation\Types\ContactTypeRelationship;
use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\Api4\Query\Comparison;
use Civi\RemoteTools\Api4\Query\CompositeCondition;
use Webmozart\Assert\Assert;

/**
 * Checks if a contact has a relationship of a given type to a contact of a
 * given type.
 */
final class ContactTypeRelationshipChecker implements ContactRelationCheckerInterface {

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
    $contactTypeId = $relationProperties['contactTypeId'];
    Assert::numeric($contactTypeId);

    $action = Relationship::get(FALSE)
      ->addSelect('id')
      ->addWhere('relationship_type_id', '=', $relationshipTypeId)
      ->addClause('OR',
        ['contact_id_a', '=', $contactId],
        ['contact_id_b', '=', $contactId],
      )
      ->addJoin('ContactType AS ct', 'INNER', NULL,
        ['ct.id', '=', $contactTypeId]
      )
      ->addJoin('Contact AS c', 'INNER', NULL,
        CompositeCondition::new('AND',
          CompositeCondition::new('OR',
            Comparison::new('c.contact_type', '=', 'ct.name'),
            Comparison::new('c.contact_sub_type', '=', 'ct.name'),
          ),
          CompositeCondition::new('OR',
            CompositeCondition::new('AND',
              Comparison::new('c.id', '=', 'contact_id_a'),
              Comparison::new('contact_id_a', '!=', $contactId),
            ),
            CompositeCondition::new('AND',
              Comparison::new('c.id', '=', 'contact_id_b'),
              Comparison::new('contact_id_b', '!=', $contactId),
            ),
          ),
        )->toArray()
      );

    return $this->api4->executeAction($action)->rowCount >= 1;
  }

  /**
   * @inheritDoc
   */
  public function supportsRelationType(string $relationType): bool {
    return ContactTypeRelationship::NAME === $relationType;
  }

}
