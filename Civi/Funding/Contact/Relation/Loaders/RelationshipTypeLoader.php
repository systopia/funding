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

namespace Civi\Funding\Contact\Relation\Loaders;

use Civi\Api4\Contact;
use Civi\Funding\Contact\RelatedContactsLoaderInterface;
use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\Api4\Query\Comparison;
use Civi\RemoteTools\Api4\Query\CompositeCondition;
use Webmozart\Assert\Assert;

/**
 * Loads all contacts to which a contact has a relation of a given type.
 */
final class RelationshipTypeLoader implements RelatedContactsLoaderInterface {

  private Api4Interface $api4;

  public function __construct(Api4Interface $api4) {
    $this->api4 = $api4;
  }

  /**
   * @inheritDoc
   */
  public function getRelatedContacts(int $contactId, string $relationType, array $relationProperties): array {
    Assert::integer($relationProperties['relationshipTypeId']);
    $relationshipTypeId = $relationProperties['relationshipTypeId'];
    $action = Contact::get()
      ->setCheckPermissions(FALSE)
      ->addJoin('Relationship AS r', 'INNER', NULL,
        CompositeCondition::new('AND',
          Comparison::new('r.relationship_type_id', '=', $relationshipTypeId),
          CompositeCondition::new('OR',
            CompositeCondition::new('AND',
              Comparison::new('r.contact_id_a', '=', $contactId),
              Comparison::new('r.contact_id_b', '=', 'id'),
            ),
            CompositeCondition::new('AND',
              Comparison::new('r.contact_id_a', '=', 'id'),
              Comparison::new('r.contact_id_b', '=', $contactId),
            ),
          ),
        )->toArray()
      );

    /** @phpstan-var array<int, array<string, mixed>> $contacts */
    $contacts = $this->api4->executeAction($action)->indexBy('id')->getArrayCopy();

    return $contacts;
  }

  public function supportsRelationType(string $relationType): bool {
    return 'RelationshipType' === $relationType;
  }

}
