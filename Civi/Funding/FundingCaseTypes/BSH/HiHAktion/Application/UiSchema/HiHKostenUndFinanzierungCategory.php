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
use Civi\RemoteTools\JsonForms\JsonFormsRule;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsCategory;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsGroup;
use Civi\RemoteTools\JsonSchema\JsonSchema;

final class HiHKostenUndFinanzierungCategory extends JsonFormsCategory {

  public function __construct(string $scopePrefix, string $currency) {
    parent::__construct('Finanzplan', [
      new HiHKostenGroup("$scopePrefix/kosten/properties", $currency),
      new JsonFormsGroup('Gesamtkosten', [
        new JsonFormsControl(
          "$scopePrefix/kosten/properties/gesamtkosten",
          "Antragssumme (Gesamtkosten) in $currency",
          'Personal- und Sachkosten'
        ),
      ]),
      new JsonFormsControl(
        "$scopePrefix/finanzierung/properties/grundsaetzlich",
        'Wie finanziert sich Ihr Träger grundsätzlich?',
      ),
      new JsonFormsControl(
        "$scopePrefix/finanzierung/properties/gesamtesProjektHiH",
        'Ich beantrage das gesamte Projektbudget über Hand in Hand.',
      ),
      new JsonFormsControl(
        "$scopePrefix/finanzierung/properties/wichtigstePositionenBeiTeilbetrag",
        <<<EOD
Falls nur ein Teilbetrag bewilligt werden kann – welche Positionen im Finanzplan
sind für Sie am wichtigsten:
EOD,
      ),
      new JsonFormsControl(
        "$scopePrefix/finanzierung/properties/andereKosten",
        'Welche anderen Kosten hat das Projekt?',
        NULL,
        NULL,
        [
          'rule' => new JsonFormsRule(
            'SHOW',
            "$scopePrefix/finanzierung/properties/gesamtesProjektHiH",
            JsonSchema::fromArray(['const' => FALSE])
          ),
        ]
      ),
      new JsonFormsControl(
        "$scopePrefix/finanzierung/properties/finanzierungZusaetzlicheKosten",
        <<<EOD
Wie finanzieren Sie die zusätzlichen Kosten? (z.B. Spenden, andere Stiftungen,
öffentliche Mittel). Sind die Mittel schon beantragt oder bewilligt?
EOD,
        NULL,
        NULL,
        [
          'rule' => new JsonFormsRule(
            'SHOW',
            "$scopePrefix/finanzierung/properties/gesamtesProjektHiH",
            JsonSchema::fromArray(['const' => FALSE])
          ),
        ]
      ),
    ]);
  }

}
