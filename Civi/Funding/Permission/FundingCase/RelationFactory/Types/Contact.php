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

use Civi\Funding\Permission\FundingCase\RelationFactory\AbstractRelationPropertiesFactoryType;
use CRM_Funding_ExtensionUtil as E;

/**
 * @codeCoverageIgnore
 */
final class Contact extends AbstractRelationPropertiesFactoryType {

  public const NAME = 'Contact';

  public static function getName(): string {
    return self::NAME;
  }

  public function getLabel(): string {
    return E::ts('Contact');
  }

  public function getTemplate(): string {
    $fieldLabel = E::ts('Contact');
    return <<<TEMPLATE
<label>$fieldLabel</label>
<input crm-entityref="{entity: 'Contact'}"
       ng-model='properties.contactId' ng-required='true'/>
TEMPLATE;
  }

  public function getHelp(): string {
    return E::ts(<<<HELP
Assign permissions for new funding cases to the specified contact.
HELP);
  }

  public function getExtra(): array {
    return [];
  }

}
