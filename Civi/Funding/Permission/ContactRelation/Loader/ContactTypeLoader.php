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

namespace Civi\Funding\Permission\ContactRelation\Loader;

use Civi\Api4\Contact;
use Civi\Funding\Permission\ContactRelation\ContactRelationLoaderInterface;
use Civi\Funding\Permission\ContactRelation\Types\ContactType;
use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\Api4\Query\Comparison;
use Civi\RemoteTools\Api4\Query\CompositeCondition;
use Webmozart\Assert\Assert;

/**
 * Loads contacts with a type that is equal to a given type.
 */
final class ContactTypeLoader implements ContactRelationLoaderInterface {

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
    $contactTypeId = $relationProperties['contactTypeId'];
    Assert::integerish($contactTypeId);
    $separator = \CRM_Core_DAO::VALUE_SEPARATOR;

    $action = Contact::get(FALSE)
      ->addJoin('ContactType AS ct', 'INNER', NULL, CompositeCondition::new('OR',
        Comparison::new('contact_type', '=', 'ct.name'),
        Comparison::new('contact_sub_type', 'LIKE', "CONCAT('%{$separator}', ct.name, '{$separator}%')")
      )->toArray())
      ->addWhere('ct.id', '=', $contactTypeId);

    /** @phpstan-var array<int, array<string, mixed>> $contacts */
    $contacts = $this->api4->executeAction($action)->indexBy('id')->getArrayCopy();

    return $contacts;
  }

  public function supportsRelationType(string $relationType): bool {
    return ContactType::NAME === $relationType;
  }

}
