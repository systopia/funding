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

namespace Civi\Funding\Permission\ContactRelation\Loader;

use Civi\Api4\Contact;
use Civi\Funding\Permission\ContactRelation\ContactRelationLoaderInterface;
use Civi\Funding\Permission\ContactRelation\Types\ContactTypeRelationship;
use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\Api4\Query\Comparison;
use Civi\RemoteTools\Api4\Query\CompositeCondition;
use Webmozart\Assert\Assert;

/**
 * Loads contacts that have a relationship of a given type to a contact of a
 * given type.
 */
final class ContactTypeRelationshipLoader implements ContactRelationLoaderInterface {

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
    $relationshipTypeId = $relationProperties['relationshipTypeId'];
    Assert::integerish($relationshipTypeId);
    $contactTypeId = $relationProperties['contactTypeId'];
    Assert::integerish($contactTypeId);

    $action = Contact::get(FALSE)
      ->addJoin('Relationship AS r', 'INNER', NULL,
        CompositeCondition::new('AND',
          Comparison::new('r.relationship_type_id', '=', $relationshipTypeId),
          CompositeCondition::new('OR',
            Comparison::new('r.contact_id_a', '=', 'id'),
            Comparison::new('r.contact_id_b', '=', 'id'),
          )
        )->toArray()
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
      )
      ->addJoin('ContactType AS ct2', 'INNER', NULL,
        CompositeCondition::new('AND',
          Comparison::new('ct2.id', '=', $contactTypeId),
          CompositeCondition::new('OR',
            Comparison::new('c2.contact_type', '=', 'ct2.name'),
            Comparison::new('c2.contact_sub_type', '=', 'ct2.name'),
          )
        )->toArray()
      );

    /** @phpstan-var array<int, array<string, mixed>> $contacts */
    $contacts = $this->api4->executeAction($action)->indexBy('id')->getArrayCopy();

    return $contacts;
  }

  public function supportsRelationType(string $relationType): bool {
    return ContactTypeRelationship::NAME === $relationType;
  }

}
