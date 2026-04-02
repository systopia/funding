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

namespace Civi\Funding\FundingCaseTypes\AuL\Personalkosten\Clearing\UiSchema;

use Civi\Funding\Entity\ApplicationCostItemEntity;
use Civi\Funding\Entity\ClearingProcessEntityBundle;
use Civi\RemoteTools\JsonForms\JsonFormsControl;
use Civi\RemoteTools\JsonForms\JsonFormsMarkup;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsGroup;

/**
 * @phpstan-type applicationCostItemsByTypeT array<string,
 *   array<string, \Civi\Funding\Entity\ApplicationCostItemEntity>>
 *   Mapping of cost item types to cost items indexed by identifier.
 */
final class PersonalkostenReceiptsUiSchema extends JsonFormsGroup {

  public function __construct(
    ClearingProcessEntityBundle $clearingProcessBundle,
    string $personalkostenBewilligt,
  ) {
    $currency = $clearingProcessBundle->getFundingProgram()->getCurrency();
    $scopePrefix = '#/properties/costItems/properties';

    $elements = [
      new JsonFormsMarkup('Bewilligte Personalkosten: ' . $personalkostenBewilligt),
      new JsonFormsControl(
        "$scopePrefix/personalkosten/properties/records/properties/personalkosten/properties/amount",
        "Tatsächliche Personalkosten in $currency",
        'Hier bitte den Betrag des tatsächlichen AG-Bruttos angeben, der gefördert werden soll.',
      ),
      new JsonFormsControl(
        "$scopePrefix/personalkosten/properties/records/properties/personalkosten/properties/amountAdmitted",
        "Anerkannte Personalkosten in $currency",
      ),
      new JsonFormsControl(
        "$scopePrefix/sachkostenpauschale/properties/records/properties/sachkostenpauschale/properties/amount",
        "Sachkostenpauschale in $currency",
      ),
    ];
    parent::__construct('Personalkosten', $elements);
  }

}
