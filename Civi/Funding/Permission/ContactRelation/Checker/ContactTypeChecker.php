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

use Civi\Api4\ContactType;
use Civi\Funding\Permission\ContactRelation\ContactRelationCheckerInterface;
use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\Api4\Query\Comparison;
use Civi\RemoteTools\Api4\Query\CompositeCondition;
use Webmozart\Assert\Assert;

/**
 * Checks if the type of a contact is equal to a given type.
 */
final class ContactTypeChecker implements ContactRelationCheckerInterface {

  private Api4Interface $api4;

  public function __construct(Api4Interface $api4) {
    $this->api4 = $api4;
  }

  /**
   * @inheritDoc
   *
   * @throws \API_Exception
   */
  public function hasRelation(int $contactId, string $relationType, array $relationProperties): bool {
    $contactTypeId = $relationProperties['contactTypeId'];
    Assert::numeric($contactTypeId);

    $action = ContactType::get()
      ->setCheckPermissions(FALSE)
      ->addSelect('id')
      ->addWhere('id', '=', $contactTypeId)
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

  public function supportsRelationType(string $relationType): bool {
    return \Civi\Funding\Permission\ContactRelation\Types\ContactType::NAME === $relationType;
  }

}
