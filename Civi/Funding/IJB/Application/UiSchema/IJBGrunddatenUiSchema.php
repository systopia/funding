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

use Civi\RemoteTools\JsonForms\Control\JsonFormsArray;
use Civi\RemoteTools\JsonForms\JsonFormsControl;
use Civi\RemoteTools\JsonForms\JsonFormsRule;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsCategory;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsGroup;
use Civi\RemoteTools\JsonSchema\JsonSchema;

final class IJBGrunddatenUiSchema extends JsonFormsCategory {

  public function __construct() {
    parent::__construct('Grunddaten', [
      new JsonFormsControl('#/properties/grunddaten/properties/titel', 'Titel'),
      new JsonFormsControl(
        '#/properties/grunddaten/properties/kurzbeschreibungDesInhalts',
        'Kurzbeschreibung des Inhalts',
        NULL,
        [
          'multi' => TRUE,
          'placeholder' => 'Maximal 500 Zeichen',
        ]
      ),
      new JsonFormsArray('#/properties/grunddaten/properties/zeitraeume', 'Zeiträume', NULL, [
        new JsonFormsControl('#/properties/beginn', 'Beginn'),
        new JsonFormsControl('#/properties/ende', 'Ende'),
      ], [
        'addButtonLabel' => 'Zeitraum hinzufügen',
        'removeButtonLabel' => 'Zeitraum entfernen',
      ]),
      new JsonFormsControl('#/properties/grunddaten/properties/programmtage', 'Programmtage'),
      new JsonFormsControl('#/properties/grunddaten/properties/artDerMassnahme', 'Art der Maßnahme'),
      new JsonFormsControl('#/properties/grunddaten/properties/begegnungsland', 'Begegnungsland'),
      new JsonFormsGroup('Wo findet die Veranstaltung statt?', [
        new JsonFormsControl('#/properties/grunddaten/properties/stadt', 'Stadt/Ort der Begegnung'),
        new JsonFormsControl('#/properties/grunddaten/properties/land', 'Land'),
      ]),
      new JsonFormsControl(
        '#/properties/grunddaten/properties/fahrtstreckeInKm',
        'Einfache Fahrtstrecke in km (abgerundet)',
        'Wegstrecke bspw. mit OpenStreetMap berechnen. Luftlinie bspw. mit www.luftlinie.org berechnen.',
        NULL,
        [
          'rule' => new JsonFormsRule(
            'SHOW',
            '#/properties/grunddaten/properties/begegnungsland',
            JsonSchema::fromArray(['const' => 'partnerland'])
          ),
        ]
      ),
    ]);
  }

}
