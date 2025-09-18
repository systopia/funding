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

namespace Civi\Funding\FundingCaseTypes\AuL\SammelantragKurs\Application\UiSchema;

use Civi\RemoteTools\JsonForms\Control\JsonFormsArray;
use Civi\RemoteTools\JsonForms\JsonFormsControl;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsCategory;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsGroup;

final class KursGrunddatenUiSchema extends JsonFormsCategory {

  /**
   * @param bool $report TRUE if used for report.
   */
  public function __construct(string $scopePrefix, bool $report = FALSE) {
    $teilnehmerElements = [
      new JsonFormsControl(
        "$scopePrefix/teilnehmer/properties/gesamt", 'Gesamtanzahl der Teilnehmer*innen',
      ),
      new JsonFormsControl("$scopePrefix/teilnehmer/properties/weiblich", 'davon weiblich'),
      new JsonFormsControl("$scopePrefix/teilnehmer/properties/divers", 'davon divers'),
      new JsonFormsControl("$scopePrefix/teilnehmer/properties/unter27", 'davon U27'),
      new JsonFormsControl(
        "$scopePrefix/teilnehmer/properties/inJugendhilfeEhrenamtlichTaetig",
        'davon in der Kinder- und Jugendhilfe (Multiplikator*innen-Seminare) ehrenamtlich tätig',
      ),
      new JsonFormsControl(
        "$scopePrefix/teilnehmer/properties/inJugendhilfeHauptamtlichTaetig",
        'davon in der Kinder- und Jugendhilfe (Multiplikator*innen-Seminare) hauptamtlich tätig',
      ),
      new JsonFormsControl(
        "$scopePrefix/teilnehmer/properties/referenten", 'davon Referent*innen',
      ),
    ];
    if ($report) {
      $teilnehmerElements[] = new JsonFormsControl(
        "$scopePrefix/teilnehmer/properties/referentenMitHonorar",
        'davon Referent*innen, bei denen tatsächlich ein Honorar gezahlt wurde',
      );
      $teilnehmerElements[] = new JsonFormsControl(
        "$scopePrefix/teilnehmer/properties/mitFahrtkosten",
        'davon Personen, bei denen tatsächlich Fahrtkosten angefallen sind',
      );
    }

    parent::__construct('Grunddaten', [
      new JsonFormsControl("$scopePrefix/internerBezeichner", 'Interner Bezeichner'),
      new JsonFormsControl("$scopePrefix/titel", 'Titel'),
      new JsonFormsControl(
        "$scopePrefix/kurzbeschreibungDerInhalte", 'Kurzbeschreibung der Kursinhalte', NULL, [
          'multi' => TRUE,
          'placeholder' => 'Kurzbeschreibung der Kursinhalte (maximal 500 Zeichen)',
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
      new JsonFormsGroup('Teilnehmer*innen', $teilnehmerElements),
      new JsonFormsControl("$scopePrefix/teilnehmertage", 'Teilnehmendentage'),
    ]);
  }

}
