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
 * Loads all contacts of a given type to which a contact has a relationship of a
 * given type.
 */
final class ContactTypeAndRelationshipTypeLoader implements RelatedContactsLoaderInterface {

  private Api4Interface $api4;

  public function __construct(Api4Interface $api4) {
    $this->api4 = $api4;
  }

  /**
   * @inheritDoc
   */
  public function getRelatedContacts(int $contactId, string $relationType, array $relationProperties): array {
    Assert::integer($relationProperties['contactTypeId']);
    Assert::integer($relationProperties['relationshipTypeId']);
    $contactTypeId = $relationProperties['contactTypeId'];
    $relationshipTypeId = $relationProperties['relationshipTypeId'];
    $separator = \CRM_Core_DAO::VALUE_SEPARATOR;

    $action = Contact::get(FALSE)
      ->addJoin('ContactType AS ct', 'INNER', NULL,
        CompositeCondition::new('AND',
          Comparison::new('ct.id', '=', $contactTypeId),
          CompositeCondition::new(
            'OR',
            Comparison::new('contact_type', '=', 'ct.name'),
            Comparison::new('contact_sub_type', 'LIKE', "CONCAT('%${separator}', ct.name, '${separator}%')")
          ),
        )->toArray()
      )->addJoin('Relationship AS r', 'INNER', NULL,
        CompositeCondition::new('AND',
          Comparison::new('r.relationship_type_id', '=', $relationshipTypeId),
          Comparison::new('r.is_active', '=', TRUE),
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

  /**
   * @inheritDoc
   */
  public function supportsRelationType(string $relationType): bool {
    return 'ContactTypeAndRelationshipType' === $relationType;
  }

}
