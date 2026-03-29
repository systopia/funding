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

namespace Civi\Funding\Permission\ContactRelation\Types;

use Civi\Api4\RelationshipType;
use Civi\Funding\Contact\Relation\AbstractRelationType;
use Civi\RemoteTools\Api4\Api4Interface;
use CRM_Funding_ExtensionUtil as E;

/**
 * @phpstan-type propertiesT array{
 *   relationships: list<array{contactId: int, relationshipTypeId: int}>
 * }
 */
final class ContactRelationships extends AbstractRelationType {

  public const NAME = 'ContactRelationships';

  private Api4Interface $api4;

  public function __construct(Api4Interface $api4) {
    $this->api4 = $api4;
  }

  public function getName(): string {
    return self::NAME;
  }

  public function getLabel(): string {
    return E::ts('Relationship to contacts');
  }

  public function getTemplate(): string {
    $relationshipTypeLabel = E::ts('Relationship type');
    $contactLabel = E::ts('Contact');
    $removeLabel = E::ts('Remove');
    $addLabel = E::ts('Add relationship');

    return <<<TEMPLATE


<div ng-repeat="relationship in properties.relationships track by \$index">
  <label>$relationshipTypeLabel</label>
  <select class="crm-form-select" ng-model="relationship.relationshipTypeId"
    ng-required="true"
    ng-options="label for (label , value) in typeSpecification.extra.relationshipTypes"></select>
  <label>$contactLabel</label>
  <input crm-entityref="{entity: 'Contact'}"
         ng-model="relationship.contactId" ng-required="true"/>
  <button ng-show="\$index !== 0" type="button" class="btn btn-sm btn-danger"
          ng-click="properties.relationships.splice(\$index, 1)">$removeLabel</button>
</div>
<div>
  <button type="button" class="btn btn-sm"
          ng-click="properties.relationships.push({})">$addLabel</button>
</div>
TEMPLATE;
  }

  public function getHelp(): string {
    return E::ts("Matches if a contact has all specified relationships.");
  }

  public function getExtra(): array {
    return [
      'relationshipTypes' => iterator_to_array($this->getRelationshipTypes()),
    ];
  }

  public function getInitialProperties(): array {
    return ['relationships' => [(object) []]];
  }

  /**
   * @phpstan-return \Traversable<string, int>
   *
   * @throws \CRM_Core_Exception
   */
  private function getRelationshipTypes(): \Traversable {
    $action = RelationshipType::get(FALSE)
      ->addSelect('id', 'label_a_b', 'label_b_a')
      ->addOrderBy('label_a_b');

    /** @phpstan-var array{id: int, label_a_b: string, label_b_a: string} $relationshipType */
    foreach ($this->api4->executeAction($action) as $relationshipType) {
      yield $relationshipType['label_a_b'] . ' / ' . $relationshipType['label_b_a']
      => $relationshipType['id'];
    }
  }

}
