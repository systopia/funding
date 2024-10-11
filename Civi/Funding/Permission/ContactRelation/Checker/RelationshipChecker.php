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

namespace Civi\Funding\Permission\ContactRelation\Checker;

use Civi\Funding\Permission\ContactRelation\ContactRelationCheckerInterface;
use Civi\Funding\Permission\ContactRelation\Types\Relationship;
use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\Api4\Query\Comparison;
use Civi\RemoteTools\Api4\Query\CompositeCondition;
use Webmozart\Assert\Assert;

/**
 * Checks if a contact has a relationship of any of the given types to a contact
 * of any of the given types that is in any of the given groups.
 *
 * All options may be empty, meaning everything is allowed. The options have
 * the following form:
 * array {
 *   relationshipTypeIds: list<int>,
 *   contactTypeIds: list<int>,
 *   groupIds: list<int>,
 * }
 */
final class RelationshipChecker implements ContactRelationCheckerInterface {

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
    $relationshipTypeIds = $relationProperties['relationshipTypeIds'];
    Assert::isArray($relationshipTypeIds);
    Assert::allIntegerish($relationshipTypeIds);
    $contactTypeIds = $relationProperties['contactTypeIds'];
    Assert::isArray($contactTypeIds);
    Assert::allIntegerish($contactTypeIds);
    $groupIds = $relationProperties['groupIds'];
    Assert::isArray($groupIds);
    Assert::allIntegerish($groupIds);

    $action = \Civi\Api4\Relationship::get(FALSE)
      ->addSelect('id')
      ->addWhere('is_active', '=', TRUE)
      ->addClause('OR',
        ['contact_id_a', '=', $contactId],
        ['contact_id_b', '=', $contactId],
      );

    if ([] !== $relationshipTypeIds) {
      $action->addWhere('relationship_type_id', 'IN', $relationshipTypeIds);
    }

    $action->addJoin('Contact AS c', 'INNER', NULL,
      CompositeCondition::new('OR',
        CompositeCondition::new('AND',
          Comparison::new('c.id', '=', 'contact_id_a'),
          Comparison::new('contact_id_a', '!=', $contactId),
        ),
        CompositeCondition::new('AND',
          Comparison::new('c.id', '=', 'contact_id_b'),
          Comparison::new('contact_id_b', '!=', $contactId),
        ),
      )->toArray()
    );

    if ([] !== $groupIds) {
      $action
        ->addJoin('GroupContact AS gc', 'INNER', NULL,
          ['gc.contact_id', '=', 'c.id'], ['gc.group_id', 'IN', $groupIds])
        ->addJoin('Group AS g', 'INNER', NULL,
          ['g.id', '=', 'gc.group_id'], ['g.is_active', '=', TRUE]);
    }

    if ([] !== $contactTypeIds) {
      $separator = \CRM_Core_DAO::VALUE_SEPARATOR;
      $action->addJoin('ContactType AS ct', 'INNER', NULL,
        CompositeCondition::new('AND',
          Comparison::new('ct.id', 'IN', $contactTypeIds),
          CompositeCondition::new('OR',
            Comparison::new('c.contact_type', '=', 'ct.name'),
            Comparison::new('c.contact_sub_type', 'LIKE', "CONCAT('%${separator}', ct.name, '${separator}%')")
          )
        )->toArray()
      );
    }

    return $this->api4->executeAction($action)->rowCount >= 1;
  }

  public function supportsRelationType(string $relationType): bool {
    return Relationship::NAME === $relationType;
  }

}
