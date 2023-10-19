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
use Civi\RemoteTools\JsonForms\Layout\JsonFormsCategory;

final class IJBBeschreibungUiSchema extends JsonFormsCategory {

  public function __construct() {
    parent::__construct('Beschreibung des Vorhabens', [
      new JsonFormsControl(
        '#/properties/beschreibung/properties/ziele',
        'Welche Ziele hat die Veranstaltung? (Mehrfachauswahl möglich)',
      ),
      new JsonFormsControl(
        '#/properties/beschreibung/properties/bildungsanteil',
        'Wie hoch ist der Bildung- und Begegnungsanteil des Vorhabens in %?',
        <<<'EOD'
Der KJP fördert nur Seminare mit <strong>überwiegendem</strong> Lehr- und
Fortbildungscharakter. Nicht förderbar sind beispielsweise Projekte, die
überwiegend der Erholung und Touristik dienen.
EOD
      ),
      new JsonFormsControl(
        '#/properties/beschreibung/properties/inhalt',
        'Inhalt und Ziel des beantragten Vorhabens: Was soll wie erreicht werden?',
        <<<EOD
Bitte beschreiben Sie hier die fachliche Zielstellung der Maßnahme, die
Themenbereiche und Programmschwerpunkte; gibt es dabei Unterschiede bezüglich
der unterschiedlichen Geschlechter der Teilnehmenden?
EOD,
        ['multi' => TRUE],
      ),
      new JsonFormsControl(
        '#/properties/beschreibung/properties/erlaeuterungen',
        'Erläuterungen zur Vor- und Nachbereitung, sprachlichen Verständigung und Öffentlichkeitsarbeit',
        <<<EOD
Bitte erläutern Sie hier Inhalt und Form der Vor- und Nachbereitung sowie die
geplanten Maßnahmen zur Sicherstellung der sprachlichen Verständigung sowie die geplante Öffentlichkeitsarbeit, mit der
auch auf die Förderung hingewiesen wird.
EOD,
        ['multi' => TRUE],
      ),
      new JsonFormsControl(
        '#/properties/beschreibung/properties/qualifikation',
        'Qualifikation der Leitungs- und Begleitpersonen',
        NULL,
        ['multi' => TRUE],
      ),
    ]);
  }

}
