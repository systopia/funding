<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
 *
 *  This program is free software: you can redistribute it and/or modify it under
 *  the terms of the GNU Affero General Public License as published by the Free
 *  Software Foundation, either version 3 of the License, or (at your option) any
 *  later version.
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

namespace Civi\Funding\FundingCaseTypes\BSH\HiHAktion\Clearing\UiSchema;

use Civi\Funding\Entity\ApplicationCostItemEntity;
use Civi\RemoteTools\JsonForms\Control\JsonFormsArray;
use Civi\RemoteTools\JsonForms\Control\JsonFormsHidden;
use Civi\RemoteTools\JsonForms\JsonFormsControl;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsGroup;

final class HiHReceiptsUiSchema extends JsonFormsGroup {

  public function __construct(
    ApplicationCostItemEntity $personalkostenBewilligt,
    ApplicationCostItemEntity $honorareBewilligt,
    ApplicationCostItemEntity $sachkostenBewilligt,
    string $currency
  ) {
    parent::__construct('Projektkosten', [
      new JsonFormsArray(
        sprintf(
          '#/properties/costItems/properties/%s/properties/records',
          $personalkostenBewilligt->getId()
        ),
        'Personalkosten',
        NULL,
        [
          new JsonFormsHidden('#/properties/_id', ['internal' => TRUE]),
          new JsonFormsControl('#/properties/properties/properties/posten', 'Posten'),
          new JsonFormsControl('#/properties/properties/properties/wochenstunden', 'Wochenstunden'),
          new JsonFormsControl(
            '#/properties/properties/properties/monatlichesArbeitgeberbrutto', 'Monatliches Arbeitgeberbrutto'
          ),
          new JsonFormsControl('#/properties/properties/properties/monate', 'Monate'),
          new JsonFormsControl('#/properties/amount', "Betrag in $currency"),
          new JsonFormsControl('#/properties/amountAdmitted', "Anerkannter Betrag in $currency"),
        ],
        ['addButtonLabel' => 'Hinzufügen']
      ),
    ]);
  }

}
