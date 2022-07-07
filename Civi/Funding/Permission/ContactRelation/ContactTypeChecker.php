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

namespace Civi\Funding\Permission\ContactRelation;

use Civi\Api4\ContactType;
use Civi\Funding\Permission\ContactRelationCheckerInterface;
use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\Api4\Query\Comparison;
use Civi\RemoteTools\Api4\Query\CompositeCondition;

final class ContactTypeChecker implements ContactRelationCheckerInterface {

  private Api4Interface $api4;

  public function __construct(Api4Interface $api4) {
    $this->api4 = $api4;
  }

  /**
   * @inheritDoc
   */
  public function hasRelation(int $contactId, array $contactRelation, ?array $parentContactRelation): bool {
    $action = ContactType::get()
      ->addSelect('id')
      ->addWhere('id', '=', $contactRelation['entity_id'])
      ->addJoin('Contact AS c', 'INNER', NULL,
        CompositeCondition::new('AND',
          Comparison::new('c.id', '=', $contactId),
          CompositeCondition::new(
            'OR',
            Comparison::new('c.contact_type', '=', 'name'),
            Comparison::new('c.contact_sub_type', '=', 'name'),
          ),
        )->toArray(),
      );

    return $this->api4->executeAction($action)->rowCount === 1;
  }

  /**
   * @inheritDoc
   */
  public function supportsRelation(array $contactRelation, ?array $parentContactRelation): bool {
    return 'civicrm_contact_type' === $contactRelation['entity_table'] && NULL === $contactRelation['parent_id'];
  }

}
