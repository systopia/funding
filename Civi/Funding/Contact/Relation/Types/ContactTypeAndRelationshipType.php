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

namespace Civi\Funding\Contact\Relation\Types;

use Civi\Api4\ContactType;
use Civi\Funding\Contact\Relation\AbstractRelationType;
use Civi\RemoteTools\Api4\Api4Interface;
use CRM_Funding_ExtensionUtil as E;

final class ContactTypeAndRelationshipType extends AbstractRelationType {

  private Api4Interface $api4;

  public function __construct(Api4Interface $api4) {
    $this->api4 = $api4;
  }

  public function getName(): string {
    return 'ContactTypeAndRelationshipType';
  }

  public function getLabel(): string {
    return E::ts('Related contacts of a specific type');
  }

  public function getTemplate(): string {
    $fieldLabelContactType = E::ts('Contact type');
    $fieldLabelRelationshipType = E::ts('Relationship type');

    return <<<TEMPLATE
<label>$fieldLabelContactType</label>
<select class="crm-form-select" ng-model="properties.contactTypeId" ng-required="true"
  ng-options="label for (label , value) in typeSpecification.extra.contactTypes"></select>
<label>$fieldLabelRelationshipType</label>
<select class="crm-form-select" ng-model="properties.relationshipTypeId" ng-required="true"
  ng-options="label for (label , value) in typeSpecification.extra.relationshipTypes"></select>
TEMPLATE;
  }

  public function getHelp(): string {
    return E::ts(<<<HELP
Contacts of the specified type are available as recipients if a relationship of
the specified type exists
HELP);
  }

  public function getExtra(): array {
    return [
      'contactTypes' => iterator_to_array($this->getContactTypes()),
      'relationshipTypes' => iterator_to_array($this->getRelationshipTypes()),
    ];
  }

  /**
   * @phpstan-return iterable<string, int>
   *
   * @throws \API_Exception
   */
  private function getContactTypes(): iterable {
    $action = ContactType::get()
      ->addSelect('id', 'label')
      ->addOrderBy('label');

    /** @phpstan-var array{id: int, label:string} $contactType */
    foreach ($this->api4->executeAction($action) as $contactType) {
      yield $contactType['label'] => $contactType['id'];
    }
  }

  /**
   * @return iterable<string, int>
   *
   * @throws \API_Exception
   */
  private function getRelationshipTypes(): iterable {
    $action = \Civi\Api4\RelationshipType::get()
      ->addSelect('id', 'label_a_b', 'label_b_a')
      ->addOrderBy('label_a_b');

    /** @phpstan-var array{id: int, label_a_b: string, label_b_a: string} $relationshipType */
    foreach ($this->api4->executeAction($action) as $relationshipType) {
      yield $relationshipType['label_a_b'] . ' / ' . $relationshipType['label_b_a']
      => $relationshipType['id'];
    }
  }

}
