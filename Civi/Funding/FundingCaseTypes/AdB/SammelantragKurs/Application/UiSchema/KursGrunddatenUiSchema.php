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

namespace Civi\Funding\FundingCaseTypes\AdB\SammelantragKurs\Application\UiSchema;

use Civi\RemoteTools\JsonForms\Control\JsonFormsArray;
use Civi\RemoteTools\JsonForms\JsonFormsControl;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsCategory;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsGroup;

final class KursGrunddatenUiSchema extends JsonFormsCategory {

  /**
   * @param bool $report TRUE if used for report.
   */
  public function __construct(string $scopePrefix, bool $report = FALSE) {
    $teilnehmerGenderElements = [
      new JsonFormsControl("$scopePrefix/teilnehmer/properties/weiblich", 'weiblich'),
      new JsonFormsControl("$scopePrefix/teilnehmer/properties/maennlich", 'männlich'),
      new JsonFormsControl("$scopePrefix/teilnehmer/properties/divers", 'divers'),
      new JsonFormsControl("$scopePrefix/teilnehmer/properties/genderKeineAngabe", 'keine Angabe'),
    ];
    $teilnehmerAlterElements = [
      new JsonFormsControl("$scopePrefix/teilnehmer/properties/alter0bis5", '0 bis 5 Jahre'),
      new JsonFormsControl("$scopePrefix/teilnehmer/properties/alter6bis9", '6 bis 9 Jahre'),
      new JsonFormsControl("$scopePrefix/teilnehmer/properties/alter10bis12", '10 bis 12 Jahre'),
      new JsonFormsControl("$scopePrefix/teilnehmer/properties/alter13bis16", '13 bis 16 Jahre'),
      new JsonFormsControl("$scopePrefix/teilnehmer/properties/alter17bis20", '17 bis 20 Jahre'),
      new JsonFormsControl("$scopePrefix/teilnehmer/properties/alter21bis26", '21 bis 26 Jahre'),
      new JsonFormsControl("$scopePrefix/teilnehmer/properties/alterAb27", 'ab 27 Jahre Jahre'),
    ];
    $teilnehmerAllgemeinElements = [
      new JsonFormsControl(
        "$scopePrefix/teilnehmer/properties/gesamt", 'Anzahl der Teilnehmenden gesamt',
      ),
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
      $teilnehmerAllgemeinElements[] = new JsonFormsControl(
        "$scopePrefix/teilnehmer/properties/referentenMitHonorar",
        'davon Referent*innen, bei denen tatsächlich ein Honorar gezahlt wurde',
      );
      $teilnehmerAllgemeinElements[] = new JsonFormsControl(
        "$scopePrefix/teilnehmer/properties/mitFahrtkosten",
        'davon Personen, bei denen tatsächlich Fahrtkosten angefallen sind',
      );
    }

    $elements = [
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
      new JsonFormsGroup(
        'Anzahl der Teilnehmenden nach Gender',
        $teilnehmerGenderElements,
        <<<EOD
Um statistisch nachweisen zu können, dass wir erfolgreich Maßnahmen des
Gendermainstreaming umsetzen, zu denen wir qua KJP-Förderung verpflichtet sind,
erheben wir entsprechend der TN-Listen Angaben zum Geschlecht. Da wir es jedoch
als problematisch ansehen eine Positionierung von Jugendlichen zu erzwingen, ist
nur die Angabe zur Anzahl weiblicher Personen in dieser Abfrage verpflichtend.
EOD
      ),
      new JsonFormsGroup(
        'Alterskohorten der Teilnehmenden',
        $teilnehmerAlterElements,
      ),
      new JsonFormsGroup(
        'Teilnehmende',
        $teilnehmerAllgemeinElements,
        <<<EOD
Bitte bei sämtlichen Daten die Referent*innen berücksichtigen. Personen, die hauptamtlich beim Träger angestellt sind,
gelten hier nicht als Teilnehmende.
EOD,
      ),
      new JsonFormsControl("$scopePrefix/teilnehmertage", 'Teilnehmendentage'),
    ];

    if (!$report) {
      array_unshift($elements, new JsonFormsControl('#/properties/nichtGefoerdert', 'Nicht geförderte Maßnahme'));
    }

    parent::__construct('Grunddaten', $elements);
  }

}
