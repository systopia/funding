<?php
/*
 * Copyright (C) 2026 SYSTOPIA GmbH
 *
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License as published by the Free
 * Software Foundation, either version 3 of the License, or (at your option) any
 * later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types = 1);

namespace Civi\Funding\FundingCaseTypes\AdB\SammelantragKurs\Report\UiSchema;

use Civi\RemoteTools\JsonForms\JsonFormsControl;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsCategory;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsGroup;

final class KursSachberichtCategory extends JsonFormsCategory {

  public function __construct(string $scopePrefix) {
    parent::__construct('Sachbericht', [
      new JsonFormsGroup(
        'Inhalt und Ziele der Aktivität/Veranstaltung',
        [
          new JsonFormsControl(
          "$scopePrefix/inhalteUndZiele",
            <<<EOD
Welche Ziele (inhaltlich, methodisch-didaktisch, strategisch) wurden mit der Veranstaltung verfolgt? Welche
(gesellschafts-politischen) Inhalte wurden mit den Teilnehmenden diskutiert/reflektiert und welche Methoden wurden
umgesetzt? Wie steht die Veranstaltung in Bezug zum Themenschwerpunkt und Vorhaben?
EOD,
            NULL,
            [
              'multi' => TRUE,
              'placeholder' => 'Mindestens 1.500 Zeichen, gerne auch mehr',
            ]
          ),
        ]
      ),
      new JsonFormsGroup(
        'Zielgruppe',
        [
          new JsonFormsControl(
          "$scopePrefix/zielgruppe",
          'Erläuterung zur Zielgruppe',
            <<<EOD
Wer waren die Teilnehmenden? An wen richtete sich die Veranstaltung primär? bspw. Schulklasse; Frauen mit
Fluchtbiographie; queere junge Menschen…
EOD,
            ['descriptionDisplay' => 'before']
          ),
        ]
      ),
      new JsonFormsGroup(
        'Auswertung der Erfahrungen bei der Vorbereitung und Durchführung der Veranstaltung',
        [
          new JsonFormsControl(
          "$scopePrefix/erfahrungen",
          <<<EOD
Welche Erfahrungen wurden bei der Vorbereitung und Durchführung der Veranstaltung gemacht? Was hat gut funktioniert; was
ggf. nicht? Wie ist aus deiner Sicht im Nachhinein die Veranstaltung und ihre Umsetzung unter Berücksichtigung der
angestrebten Ziele insgesamt einzuschätzen? Bitte berücksichtige dabei auch die Rückmeldungen der Teilnehmenden
EOD,
          NULL,
          ['multi' => TRUE],
          ),
          new JsonFormsControl(
          "$scopePrefix/einbeziehungTeilnehmende",
            <<<EOD
Wie wurden Teilnehmende bei der Konzeption und Umsetzung einbezogen? (Rückmeldung der TN hierzu einbeziehen)
EOD,
            NULL,
            ['multi' => TRUE]
          ),
        ]
      ),
      new JsonFormsGroup(
        'Inklusives und diversitätsorientiertes Vorgehen', [
          new JsonFormsControl(
          "$scopePrefix/inklusivesVorgehen",
          <<<EOD
Wie wurde (durch sprachliches, inhaltliches, methodisches Vorgehen) darauf geachtet, dass sich möglichst jede*r
Teilnehmende einbringen konnte und wohl gefühlt hat? Welche Maßnahmen wurden getroffen, um auf organisatorischer,
inhaltlicher und/oder methodischer Ebene eine Diversiätssensible und -orientierte Umsetzung zu berücksichtigen?
EOD,
          NULL,
          ['multi' => TRUE],
          ),
        ]),
      new JsonFormsGroup(
        'Ergebnisse, Fortführung, Ausblick',
        [
          new JsonFormsControl(
          "$scopePrefix/ergebnisse",
          'Ergebnisse, Fortführung, Ausblick',
            <<<EOD
Welche Ergebnisse, konkrete Schritte oder neue Themenstellungen sind aus der Veranstaltung entstanden? Wie werden diese
zukünftig weiter bearbeitet oder fortgeführt?
EOD,
            [
              'multi' => TRUE,
              'descriptionDisplay' => 'before',
              'placeholder' => 'Mindestens 600 Zeichen (gerne auch mehr)',
            ]
          ),
        ]
      ),
      new JsonFormsGroup(
        'Kursinhalte in Stichpunkten',
        [
          new JsonFormsControl(
            "$scopePrefix/kursinhalte",
            <<<EOD
    Bitte in kurzen Stichworten die Inhalte des Kurses zusammenfassen – max. 200 Zeichen. Die Angaben werden für den
    KJP-Verwendungsnachweis benötigt.
    EOD,
          ),
        ]
      ),
    ]);
  }

}
