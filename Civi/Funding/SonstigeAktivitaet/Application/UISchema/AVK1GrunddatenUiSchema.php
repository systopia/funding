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

namespace Civi\Funding\SonstigeAktivitaet\Application\UISchema;

use Civi\RemoteTools\JsonForms\Control\JsonFormsArray;
use Civi\RemoteTools\JsonForms\JsonFormsControl;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsCategory;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsGroup;

final class AVK1GrunddatenUiSchema extends JsonFormsCategory {

  public function __construct() {
    $elements = [
      new JsonFormsControl(
        '#/properties/grunddaten/properties/titel',
        'Titel'
      ),
      new JsonFormsControl(
        '#/properties/grunddaten/properties/kurzbeschreibungDesInhalts',
        'Kurzbeschreibung des Inhalts',
        NULL,
        [
          'multi' => TRUE,
          'placeholder' => 'Maximal 500 Zeichen',
        ]
      ),
      new JsonFormsArray(
        '#/properties/grunddaten/properties/zeitraeume',
        'Zeiträume',
        NULL,
        [
          new JsonFormsControl('#/properties/beginn', 'Beginn'),
          new JsonFormsControl('#/properties/ende', 'Ende'),
        ],
        [
          'addButtonLabel' => 'Zeitraum hinzufügen',
          'removeButtonLabel' => 'Zeitraum entfernen',
        ]
      ),
      new JsonFormsGroup('Teilnehmer*innen', [
        new JsonFormsControl(
          '#/properties/grunddaten/properties/teilnehmer/properties/gesamt',
          'Gesamtanzahl der Teilnehmer*innen'
        ),
        new JsonFormsControl(
          '#/properties/grunddaten/properties/teilnehmer/properties/weiblich',
          'davon weiblich'
        ),
        new JsonFormsControl(
          '#/properties/grunddaten/properties/teilnehmer/properties/divers',
          'davon divers'
        ),
        new JsonFormsControl(
          '#/properties/grunddaten/properties/teilnehmer/properties/unter27',
          'davon U27'
        ),
        new JsonFormsControl(
          '#/properties/grunddaten/properties/teilnehmer/properties/inJugendhilfeTaetig',
          'davon in der Kinder- und Jugendhilfe (Multiplikator*innen-Seminare) tätig'
        ),
        new JsonFormsControl(
          '#/properties/grunddaten/properties/teilnehmer/properties/referenten',
          'davon Referent*innen'
        ),
      ], 'Wie viele Teilnehmer*innen werden für die Veranstaltung erwartet?'),
    ];

    parent::__construct(
      'Grunddaten',
      $elements,
    );
  }

}
