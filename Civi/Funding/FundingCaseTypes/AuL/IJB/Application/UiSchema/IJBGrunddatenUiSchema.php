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

namespace Civi\Funding\FundingCaseTypes\AuL\IJB\Application\UiSchema;

use Civi\RemoteTools\JsonForms\Control\JsonFormsArray;
use Civi\RemoteTools\JsonForms\JsonFormsControl;
use Civi\RemoteTools\JsonForms\JsonFormsRule;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsCategory;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsGroup;
use Civi\RemoteTools\JsonSchema\JsonSchema;

final class IJBGrunddatenUiSchema extends JsonFormsCategory {

  /**
   * @param bool $report TRUE if used for report.
   */
  public function __construct(string $scopePrefix, bool $report = FALSE) {
    $elements = [
      new JsonFormsControl("$scopePrefix/internerBezeichner", 'Interner Bezeichner'),
      new JsonFormsControl("$scopePrefix/titel", 'Titel'),
      new JsonFormsControl(
        "$scopePrefix/kurzbeschreibungDesInhalts",
        'Kurzbeschreibung des Inhalts',
        NULL,
        [
          'multi' => TRUE,
          'placeholder' => 'Maximal 500 Zeichen',
        ]
      ),
      new JsonFormsArray("$scopePrefix/zeitraeume", 'Zeiträume', NULL, [
        new JsonFormsControl('#/properties/beginn', 'Beginn'),
        new JsonFormsControl('#/properties/ende', 'Ende'),
      ], [
        'addButtonLabel' => 'Zeitraum hinzufügen',
        'removeButtonLabel' => 'Zeitraum entfernen',
      ]),
      new JsonFormsControl("$scopePrefix/programmtage", 'Programmtage'),
    ];

    if ($report) {
      $deutschlandRule = new JsonFormsRule(
        'SHOW',
        "$scopePrefix/begegnungsland",
        JsonSchema::fromArray(['const' => 'deutschland'])
      );

      $elements[] = new JsonFormsControl(
        "$scopePrefix/programmtageMitHonorar",
        'davon Tage, für die bei Inlandsmaßnahmen Honorare für Sprachmittlung/Dolmetschung ausgezahlt wurden',
        NULL,
        ['rule' => $deutschlandRule]
      );
    }

    $elements = array_merge($elements, [
      new JsonFormsControl("$scopePrefix/artDerMassnahme", 'Art der Maßnahme'),
      new JsonFormsControl("$scopePrefix/begegnungsland", 'Begegnungsland'),
      new JsonFormsGroup('Wo findet die Veranstaltung statt?', [
        new JsonFormsControl("$scopePrefix/stadt", 'Stadt/Ort der Begegnung'),
        new JsonFormsControl("$scopePrefix/land", 'Land'),
      ]),
      new JsonFormsControl(
        "$scopePrefix/fahrtstreckeInKm",
        'Einfache Fahrtstrecke in km (abgerundet)',
        'Wegstrecke bspw. mit OpenStreetMap berechnen. Luftlinie bspw. mit www.luftlinie.org berechnen.',
        NULL,
        [
          'rule' => new JsonFormsRule(
            'SHOW',
            "$scopePrefix/begegnungsland",
            JsonSchema::fromArray(['const' => 'partnerland'])
          ),
        ]
      ),
    ]);

    parent::__construct('Grunddaten', $elements);
  }

}
