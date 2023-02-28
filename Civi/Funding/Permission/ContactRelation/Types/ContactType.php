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

use Civi\Funding\Contact\Relation\AbstractRelationType;
use Civi\RemoteTools\Api4\Api4Interface;
use CRM_Funding_ExtensionUtil as E;

final class ContactType extends AbstractRelationType {

  public const NAME = 'ContactType';

  private Api4Interface $api4;

  public function __construct(Api4Interface $api4) {
    $this->api4 = $api4;
  }

  public function getName(): string {
    return self::NAME;
  }

  public function getLabel(): string {
    return E::ts('Contact type');
  }

  public function getTemplate(): string {
    $fieldLabel = E::ts('Contact type');

    return <<<TEMPLATE
<label>$fieldLabel</label>
<select class="crm-form-select" ng-model="properties.contactTypeId" ng-required="true"
  ng-options="label for (label , value) in typeSpecification.extra.contactTypes"></select>
TEMPLATE;
  }

  public function getHelp(): string {
    return E::ts(<<<HELP
Matches if a contact has the specified contact type.
HELP);
  }

  public function getExtra(): array {
    return [
      'contactTypes' => iterator_to_array($this->getContactTypes()),
    ];
  }

  /**
   * @phpstan-return iterable<string, int>
   *
   * @throws \CRM_Core_Exception
   */
  private function getContactTypes(): iterable {
    $action = \Civi\Api4\ContactType::get(FALSE)
      ->addSelect('id', 'label')
      ->addOrderBy('label');

    /** @phpstan-var array{id: int, label:string} $contactType */
    foreach ($this->api4->executeAction($action) as $contactType) {
      yield $contactType['label'] => $contactType['id'];
    }
  }

}
