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

namespace Civi\Funding\Permission\ContactRelation\Loader;

use Civi\Api4\Contact;
use Civi\Funding\Permission\ContactRelation\ContactRelationLoaderInterface;
use Civi\Funding\Permission\ContactRelation\Types\Relationship;
use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\Api4\Query\Comparison;
use Civi\RemoteTools\Api4\Query\CompositeCondition;
use Webmozart\Assert\Assert;

/**
 * Loads contacts that have a relationship of any of the given types to a
 * contact of any of the given types that is in any of the given groups.
 *
 * All options may be empty, meaning everything is allowed. The options have
 * the following form:
 * array {
 *   relationshipTypeIds: list<int>,
 *   contactTypeIds: list<int>,
 *   groupIds: list<int>,
 * }
 */
final class RelationshipLoader implements ContactRelationLoaderInterface {

  private Api4Interface $api4;

  public function __construct(Api4Interface $api4) {
    $this->api4 = $api4;
  }

  /**
   * @inheritDoc
   *
   * @throws \CRM_Core_Exception
   */
  public function getContacts(string $relationType, array $relationProperties): array {
    $relationshipTypeIds = $relationProperties['relationshipTypeIds'];
    Assert::isArray($relationshipTypeIds);
    Assert::allIntegerish($relationshipTypeIds);
    $contactTypeIds = $relationProperties['contactTypeIds'];
    Assert::isArray($contactTypeIds);
    Assert::allIntegerish($contactTypeIds);
    $groupIds = $relationProperties['groupIds'];
    Assert::isArray($groupIds);
    Assert::allIntegerish($groupIds);

    $relationshipJoinConditions = [
      Comparison::new('r.is_active', '=', TRUE),
      CompositeCondition::new('OR',
        Comparison::new('r.contact_id_a', '=', 'id'),
        Comparison::new('r.contact_id_b', '=', 'id'),
      ),
    ];
    if ([] !== $relationshipTypeIds) {
      $relationshipJoinConditions[] = Comparison::new('r.relationship_type_id', 'IN', $relationshipTypeIds);
    }

    $action = Contact::get(FALSE)
      ->addJoin('Relationship AS r', 'INNER', NULL,
        CompositeCondition::new('AND', ...$relationshipJoinConditions)->toArray()
      )
      ->addJoin('Contact AS c2', 'INNER', NULL,
        CompositeCondition::new('OR',
          CompositeCondition::new('AND',
            Comparison::new('r.contact_id_a', '=', 'id'),
            Comparison::new('r.contact_id_b', '=', 'c2.id'),
          ),
          CompositeCondition::new('AND',
            Comparison::new('r.contact_id_a', '=', 'c2.id'),
            Comparison::new('r.contact_id_b', '=', 'id'),
          ),
        )->toArray()
      );

    if ([] !== $contactTypeIds) {
      $action->addJoin('ContactType AS ct2', 'INNER', NULL,
        CompositeCondition::new('AND',
          Comparison::new('ct2.id', 'IN', $contactTypeIds),
          CompositeCondition::new('OR',
            Comparison::new('c2.contact_type', '=', 'ct2.name'),
            Comparison::new('c2.contact_sub_type', '=', 'ct2.name'),
          )
        )->toArray()
      );
    }

    if ([] !== $groupIds) {
      $action
        ->addJoin('GroupContact AS gc', 'INNER', NULL,
          ['gc.contact_id', '=', 'c2.id'], ['gc.group_id', 'IN', $groupIds])
        ->addJoin('Group AS g', 'INNER', NULL,
          ['g.id', '=', 'gc.group_id'], ['g.is_active', '=', TRUE]);
    }

    /** @phpstan-var array<int, array<string, mixed>> $contacts */
    $contacts = $this->api4->executeAction($action)->indexBy('id')->getArrayCopy();

    return $contacts;
  }

  public function supportsRelationType(string $relationType): bool {
    return Relationship::NAME === $relationType;
  }

}
