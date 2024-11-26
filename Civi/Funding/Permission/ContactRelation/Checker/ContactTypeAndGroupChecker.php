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

use Civi\Api4\Contact;
use Civi\Funding\Permission\ContactRelation\ContactRelationCheckerInterface;
use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\Api4\Query\Comparison;
use Civi\RemoteTools\Api4\Query\CompositeCondition;
use Webmozart\Assert\Assert;

/**
 * Checks if the type of a contact is in one of the given types and the contact
 * is in one of the given groups.
 */
final class ContactTypeAndGroupChecker implements ContactRelationCheckerInterface {

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
    $contactTypeIds = $relationProperties['contactTypeIds'] ?? [];
    Assert::isArray($contactTypeIds);
    Assert::allIntegerish($contactTypeIds);
    $groupIds = $relationProperties['groupIds'] ?? [];
    Assert::isArray($groupIds);
    Assert::allIntegerish($groupIds);

    $action = Contact::get(FALSE)
      ->addWhere('id', '=', $contactId);

    if ([] !== $contactTypeIds) {
      $separator = \CRM_Core_DAO::VALUE_SEPARATOR;
      $action->addJoin('ContactType AS ct', 'INNER', NULL,
        CompositeCondition::new('AND',
          Comparison::new('ct.id', 'IN', $contactTypeIds),
          CompositeCondition::new(
            'OR',
            Comparison::new('contact_type', '=', 'ct.name'),
            Comparison::new('contact_sub_type', 'LIKE', "CONCAT('%{$separator}', ct.name, '{$separator}%')")
          ),
        )->toArray()
      );
    }

    if ([] !== $groupIds) {
      $action
        ->addJoin('GroupContact AS gc', 'INNER', NULL,
          ['gc.contact_id', '=', 'id'], ['gc.group_id', 'IN', $groupIds])
        ->addJoin('Group AS g', 'INNER', NULL,
          ['g.id', '=', 'gc.group_id'], ['g.is_active', '=', TRUE]);
    }

    return $this->api4->executeAction($action)->rowCount === 1;
  }

  public function supportsRelationType(string $relationType): bool {
    return \Civi\Funding\Permission\ContactRelation\Types\ContactTypeAndGroup::NAME === $relationType;
  }

}
