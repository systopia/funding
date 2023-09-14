<?php
/*
 * Copyright (C) 2023 SYSTOPIA GmbH
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

namespace Civi\Funding\IJB\Application\UiSchema;

use Civi\RemoteTools\JsonForms\JsonFormsControl;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsGroup;

final class IJBKostenUiSchema extends JsonFormsGroup {

  public function __construct(string $currency) {
    parent::__construct('Kosten', [
      new JsonFormsGroup('Unterkunft und Verpflegung', [
        new JsonFormsControl(
          '#/properties/kosten/properties/unterkunftUndVerpflegung', 'Unterkunft und Verpflegung in ' . $currency
        ),
      ], 'Hier können Sie die Kosten für Unterbringung und Verpflegung angeben.'),
      new IJBHonorareUiSchema($currency),
      new IJBFahrtkostenUiSchema($currency),
      new JsonFormsGroup('Programmkosten', [
        new JsonFormsControl(
          '#/properties/kosten/properties/programmkosten/properties/programmkosten', 'Programmkosten in ' . $currency,
        ),
        new JsonFormsControl(
          '#/properties/kosten/properties/programmkosten/properties/arbeitsmaterial', 'Arbeitsmaterial in ' . $currency,
        ),
        new JsonFormsControl(
          '#/properties/kosten/properties/programmkostenGesamt', 'Programmkosten gesamt in ' . $currency,
        ),
      ]),
      new IJBSonstigeKostenUiSchema($currency),
      new IJBSonstigeAusgabenUiSchema($currency),
      new IJBZuschlagsrelevanteKostenUiSchema($currency),
      new JsonFormsGroup('Gesamtkosten', [
        new JsonFormsControl(
          '#/properties/kosten/properties/kostenGesamt',
          'Gesamtkosten der antragstellenden Organisation in ' . $currency
        ),
      ]),
    ]);
  }

}
