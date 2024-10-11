<?php
/*
 * Copyright (C) 2024 SYSTOPIA GmbH
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
use Civi\Api4\Group;
use Civi\Funding\Contact\Relation\AbstractRelationType;
use Civi\RemoteTools\Api4\Api4Interface;
use CRM_Funding_ExtensionUtil as E;

final class ContactTypeAndGroup extends AbstractRelationType {

  public const NAME = 'ContactTypeAndGroup';

  private Api4Interface $api4;

  public function __construct(Api4Interface $api4) {
    $this->api4 = $api4;
  }

  public function getName(): string {
    return self::NAME;
  }

  public function getLabel(): string {
    return E::ts('Contact type and group');
  }

  public function getTemplate(): string {
    $fieldLabelContactTypes = E::ts('Contact types');
    $fieldLabelGroups = E::ts('Groups');
    $placeholderAny = E::ts('any');

    return <<<TEMPLATE
<label>$fieldLabelContactTypes</label>
<span style="white-space: nowrap">
  <select style="max-width: 300px"
          crm-ui-select="{allowClear: true, placeholder: '$placeholderAny', dropdownAutoWidth: true, width: 'auto'}"
          ng-model="properties.contactTypeIds" multiple>
    <option ng-repeat="(label , value) in typeSpecification.extra.contactTypes" value="{{ value }}">{{ label }}</option>
  </select>
</span>
<span style="white-space: nowrap">
  <label>$fieldLabelGroups</label>
  <select style="max-width: 300px"
          crm-ui-select="{allowClear: true, placeholder: '$placeholderAny', dropdownAutoWidth: true, width: 'auto'}"
          ng-model="properties.groupIds" multiple>
    <option ng-repeat="(label , value) in typeSpecification.extra.groups" value="{{ value }}">{{ label }}</option>
  </select>
</span>
TEMPLATE;
  }

  public function getHelp(): string {
    return E::ts(<<<HELP
Contacts with any of the specified types in any of the specified groups.
HELP);
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function getExtra(): array {
    return [
      'contactTypes' => $this->getContactTypes(),
      'groups' => $this->getGroups(),
    ];
  }

  /**
   * @phpstan-return array<string, int>
   *
   * @throws \CRM_Core_Exception
   */
  private function getContactTypes(): array {
    return $this->api4->execute(ContactType::getEntityName(), 'get', [
      'select' => ['id', 'label'],
      'orderBy' => ['label' => 'ASC'],
    ])->indexBy('label')->column('id');
  }

  /**
   * @phpstan-return array<string, int>
   *
   * @throws \CRM_Core_Exception
   */
  private function getGroups(): array {
    return $this->api4->execute(Group::getEntityName(), 'get', [
      'select' => ['id', 'title'],
      'orderBy' => ['title' => 'ASC'],
    ])->indexBy('title')->column('id');
  }

}
