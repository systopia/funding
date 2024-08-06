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

namespace Civi\Funding\FundingCaseTypes\BSH\HiHAktion\Application\UiSchema;

use Civi\RemoteTools\JsonForms\JsonFormsControl;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsCategory;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsGroup;

final class HiHKostenUndFinanzierungCategory extends JsonFormsCategory {

  public function __construct(string $scopePrefix, string $currency) {
    parent::__construct('Ausgaben und Einnahmen', [
      new HiHKostenGroup("$scopePrefix/kosten/properties", $currency),
      new HiHEinnahmenGroup("$scopePrefix/einnahmen/properties", $currency),
      new JsonFormsGroup('Gesamteinnahmen', [
        new JsonFormsControl(
          "$scopePrefix/einnahmen/properties/gesamteinnahmen",
          "Gesamteinnahmen in $currency"
        ),
      ]),
      new JsonFormsGroup('Gesamtkosten', [
        new JsonFormsControl(
          "$scopePrefix/kosten/properties/gesamtkosten",
          "Gesamtkosten in $currency",
          'Personal- und Sachkosten'
        ),
      ]),
      new JsonFormsGroup('Differenz zwischen Gesamteinnahmen und Gesamtkosten', [
        new JsonFormsControl(
          "$scopePrefix/einnahmen/properties/einnahmenKostenDifferenz",
          "Differenz in $currency",
          <<<EOD
Bitte beachten Sie, dass die Gesamteinnahmen und -ausgaben gleich sein und damit
Null ergeben müssen. Wenn das nicht der Fall ist, überprüfen Sie bitte Ihre
Eingaben und korrigieren Sie sie.
EOD

        ),
      ]),
    ]);
  }

}
