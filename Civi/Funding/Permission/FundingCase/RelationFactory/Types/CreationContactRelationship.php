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

namespace Civi\Funding\Permission\FundingCase\RelationFactory\Types;

use Civi\Api4\RelationshipType;
use Civi\Funding\Permission\FundingCase\RelationFactory\AbstractRelationPropertiesFactoryType;
use Civi\RemoteTools\Api4\Api4Interface;
use CRM_Funding_ExtensionUtil as E;

/**
 * @codeCoverageIgnore
 */
final class CreationContactRelationship extends AbstractRelationPropertiesFactoryType {

  public const NAME = 'CreationContactRelationship';

  private Api4Interface $api4;

  public function __construct(Api4Interface $api4) {
    $this->api4 = $api4;
  }

  public static function getName(): string {
    return self::NAME;
  }

  public function getLabel(): string {
    return E::ts('Relationship to creation contact');
  }

  public function getTemplate(): string {
    $relationshipTypeLabel = E::ts('Relationship type');

    return <<<TEMPLATE
<label>$relationshipTypeLabel</label>
<select class="crm-form-select" ng-model="properties.relationshipTypeId" ng-required="true"
  ng-options="label for (label , value) in typeSpecification.extra.relationshipTypes"></select>
TEMPLATE;
  }

  public function getHelp(): string {
    return E::ts(<<<HELP
Assign permissions for new funding cases to contacts that have a relationship
of the specified type to the creation contact.
HELP);
  }

  public function getExtra(): array {
    return [
      'relationshipTypes' => iterator_to_array($this->getRelationshipTypes()),
    ];
  }

  /**
   * @return iterable<string, int>
   *
   * @throws \API_Exception
   */
  private function getRelationshipTypes(): iterable {
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
