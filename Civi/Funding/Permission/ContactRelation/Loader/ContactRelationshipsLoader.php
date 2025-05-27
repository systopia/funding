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

namespace Civi\Funding\Permission\ContactRelation\Loader;

use Civi\Api4\Contact;
use Civi\Funding\Permission\ContactRelation\ContactRelationLoaderInterface;
use Civi\Funding\Permission\ContactRelation\Types\ContactRelationships;
use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\Api4\Query\Comparison;
use Civi\RemoteTools\Api4\Query\CompositeCondition;
use Webmozart\Assert\Assert;

/**
 * Loads contacts that have all given relationships specified by relationship
 * type and contact ID.
 */
final class ContactRelationshipsLoader implements ContactRelationLoaderInterface {

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
    $relationships = $relationProperties['relationships'];
    Assert::isArray($relationships);
    Assert::notEmpty($relationships);

    $action = Contact::get(FALSE);

    foreach ($relationships as $index => $relationship) {
      Assert::integer($index);
      Assert::integerish($relationship['contactId']);
      Assert::integerish($relationship['relationshipTypeId']);

      $action->addJoin("Relationship AS r$index", 'INNER', NULL,
        CompositeCondition::new('OR',
          CompositeCondition::new('AND',
            Comparison::new("r$index.contact_id_a", '=', 'id'),
            Comparison::new("r$index.contact_id_b", '=', $relationship['contactId']),
            Comparison::new("r$index.relationship_type_id", '=', $relationship['relationshipTypeId']),
            Comparison::new("r$index.is_active", '=', TRUE),
          ),
          CompositeCondition::new('AND',
            Comparison::new("r$index.contact_id_a", '=', $relationship['contactId']),
            Comparison::new("r$index.contact_id_b", '=', 'id'),
            Comparison::new("r$index.relationship_type_id", '=', $relationship['relationshipTypeId']),
            Comparison::new("r$index.is_active", '=', TRUE),
          ),
        )->toArray()
      );
    }

    /** @phpstan-var array<int, array<string, mixed>> $contacts */
    $contacts = $this->api4->executeAction($action)->indexBy('id')->getArrayCopy();

    return $contacts;
  }

  public function supportsRelationType(string $relationType): bool {
    return ContactRelationships::NAME === $relationType;
  }

}
