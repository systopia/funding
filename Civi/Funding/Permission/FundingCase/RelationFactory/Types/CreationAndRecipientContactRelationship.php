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

namespace Civi\Funding\Permission\FundingCase\RelationFactory\Types;

use Civi\Api4\RelationshipType;
use Civi\Funding\Permission\FundingCase\RelationFactory\AbstractRelationPropertiesFactoryType;
use Civi\RemoteTools\Api4\Api4Interface;
use CRM_Funding_ExtensionUtil as E;

/**
 * @codeCoverageIgnore
 */
final class CreationAndRecipientContactRelationship extends AbstractRelationPropertiesFactoryType {

  public const NAME = 'CreationAndRecipientContactRelationship';

  private Api4Interface $api4;

  public function __construct(Api4Interface $api4) {
    $this->api4 = $api4;
  }

  public static function getName(): string {
    return self::NAME;
  }

  public function getLabel(): string {
    return E::ts('Relationship to creation and recipient contact');
  }

  public function getTemplate(): string {
    $creationContactRelationshipTypeLabel = E::ts('Creator relationship type');
    $recipientContactRelationshipTypeLabel = E::ts('Recipient relationship type');

    return <<<TEMPLATE
<label>$creationContactRelationshipTypeLabel</label>
<select class="crm-form-select" ng-model="properties.creationContactRelationshipTypeId" ng-required="true"
  ng-options="label for (label , value) in typeSpecification.extra.relationshipTypes"></select>
<label>$recipientContactRelationshipTypeLabel</label>
<select class="crm-form-select" ng-model="properties.recipientContactRelationshipTypeId" ng-required="true"
  ng-options="label for (label , value) in typeSpecification.extra.relationshipTypes"></select>
TEMPLATE;
  }

  public function getHelp(): string {
    return E::ts("Assign permissions for new funding cases to contacts that have a relationship of the specified type to the creation contact and a relationship of the specified type to the recipient contact.");
  }

  public function getExtra(): array {
    return [
      'relationshipTypes' => iterator_to_array($this->getRelationshipTypes()),
    ];
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
