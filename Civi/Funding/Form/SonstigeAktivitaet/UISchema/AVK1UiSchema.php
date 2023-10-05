<?php
/*
 * Copyright (C) 2022 SYSTOPIA GmbH
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

namespace Civi\Funding\Form\SonstigeAktivitaet\UISchema;

use Civi\RemoteTools\JsonForms\Control\JsonFormsArray;
use Civi\RemoteTools\JsonForms\Control\JsonFormsHidden;
use Civi\RemoteTools\JsonForms\JsonFormsControl;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsCloseableGroup;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsGroup;

/**
 * This implements the UI schema for an "AV-K1" form to apply for a funding for
 * a "Sonstige Aktivität" in the "Kinder- und Jugendplan des Bundes (KJP)".
 * Because it is a specific German form strings are not translated.
 */
final class AVK1UiSchema extends JsonFormsGroup {

  /**
   * @phpstan-param array<int, \Civi\RemoteTools\JsonForms\Control\JsonFormsSubmitButton> $submitButtons
   * @phpstan-param array<int, \Civi\RemoteTools\JsonForms\JsonFormsElement> $preSubmitButtonElements
   */
  public function __construct(string $currency, array $submitButtons, array $preSubmitButtonElements = []) {
    parent::__construct('Förderantrag für sonstige Aktivität', [
      new AVK1GrunddatenUiSchema(),
      new JsonFormsCloseableGroup('Antragstellende Organisation', [
        new JsonFormsControl('#/properties/empfaenger', ''),
      ]),
      new JsonFormsCloseableGroup('Kosten und Finanzierung', [
        // Abschnitt I
        new JsonFormsGroup('Kosten', [
          // Abschnitt I.1
          new JsonFormsGroup('Unterkunft und Verpflegung', [
            new JsonFormsControl(
              '#/properties/kosten/properties/unterkunftUndVerpflegung', 'Unterkunft und Verpflegung in ' . $currency
            ),
          ], 'Hier können Sie die Kosten für Unterbringung und Verpflegung angeben.'),
          // Abschnitt I.2
          new AVK1HonorareUiSchema($currency),
          // Abschnitt I.4
          new AVK1FahrtkostenUiSchema($currency),
          // Abschnitt I.5
          new AVK1SachkostenUiSchema($currency),
          // Abschnitt I.6
          new AVK1SonstigeAusgabenUiSchema($currency),
          // Abschnitt I.7
          new JsonFormsGroup('Nur bei internationalen Maßnahmen', [
            new JsonFormsControl(
              '#/properties/kosten/properties/versicherung/properties/teilnehmer',
              'Kosten der Versicherung der Teilnehmer*innen in ' . $currency
            ),
          ], 'Nur bei internationalen Maßnahmen'),
          new JsonFormsGroup('Gesamtkosten', [
            new JsonFormsControl(
              '#/properties/kosten/properties/gesamtkosten', 'Gesamtkosten in ' . $currency
            ),
          ]),
        ]),
        // Abschnitt II
        new JsonFormsGroup('Finanzierung', [
          // Abschnitt II.2
          new JsonFormsGroup('Teilnehmer*innenbeiträge', [
            new JsonFormsControl(
              '#/properties/finanzierung/properties/teilnehmerbeitraege', 'Teilnehmer*innenbeiträge in ' . $currency
            ),
          ], 'Bitte geben Sie an, wie viel durch die Teilnehmer*innenbeiträge eingenommen wird'),
          // Abschnitt II.2
          new JsonFormsGroup('Eigenmittel', [
            new JsonFormsControl(
              '#/properties/finanzierung/properties/eigenmittel', 'Eigenmittel in ' . $currency
            ),
          ], 'Bitte geben Sie hier die Eigenmittel an, die Sie für Ihr Vorhaben aufbringen können.'),
          // Abschnitt II.3
          new AVK1OeffentlicheMittelUiSchema($currency),
          // Abschnitt II.4
          new AVK1SonstigeMittelUiSchema($currency),
          new JsonFormsGroup('Gesamtfinanzierung und beantragter Zuschuss', [
            // Gesamtmittel ohne Zuschuss
            new JsonFormsControl(
              '#/properties/finanzierung/properties/gesamtmittel', 'Gesamtfinanzierung in ' . $currency
            ),
            // Abschnitt II.5
            new JsonFormsControl(
              '#/properties/finanzierung/properties/beantragterZuschuss', 'Beantragter Zuschuss in ' . $currency
            ),
          ]),
        ], <<<'EOD'
Eine Vollförderung ist bei den Sonstigen Aktivitäten nicht möglich. Bitte geben
Sie deswegen bei mindestens einer der folgenden Kategorien Mittel an.
EOD
),
      ]),
      // Beschreibung des Vorhabens (not part of default "AV-K1")
      new JsonFormsCloseableGroup('Beschreibung des Vorhabens', [
        new JsonFormsControl(
          '#/properties/beschreibung/properties/thematischeSchwerpunkte',
          'Welche thematischen Schwerpunkte hat die Veranstaltung?',
          NULL,
          ['multi' => TRUE]
        ),
        new JsonFormsControl(
          '#/properties/beschreibung/properties/geplanterAblauf',
          'Wie ist der Ablauf der Veranstaltung geplant?',
          <<<EOD
Bei den Projektunterlagen unten können auch Dokumente hochgeladen werden, die
den Ablauf beschreiben
EOD,
          ['multi' => TRUE]
        ),
        new JsonFormsControl(
          '#/properties/beschreibung/properties/beitragZuPolitischerJugendbildung',
          'Welchen Beitrag leistet die Veranstaltung zur Politischen Jugendbildung?',
          NULL,
          ['multi' => TRUE]
        ),
        new JsonFormsControl(
          '#/properties/beschreibung/properties/zielgruppe',
          'Welche Zielgruppe soll mit der Veranstaltung erreicht werden (Zusammensetzung, Alter)?',
          NULL,
          ['multi' => TRUE]
        ),
        new JsonFormsControl(
          '#/properties/beschreibung/properties/ziele', 'Welche Ziele hat die Veranstaltung? (Mehrfachauswahl möglich)'
        ),
        new JsonFormsControl(
          '#/properties/beschreibung/properties/bildungsanteil', 'Wie hoch ist der Bildungsanteil des Vorhabens in %?'
        ),
        new JsonFormsControl(
          '#/properties/beschreibung/properties/veranstaltungsort', 'Wo findet die Veranstaltung statt?'
        ),
        new JsonFormsControl(
          '#/properties/beschreibung/properties/partner', 'Mit welcher Schule oder Organisation wird kooperiert?'
        ),
      ]),
      new JsonFormsCloseableGroup('Projektunterlagen', [
        new JsonFormsArray('#/properties/projektunterlagen', 'Dokumente',
          'Hier können Unterlagen wie das geplante Programm mit Zeitangaben oder die Ausschreibung' .
          ' hochgeladen werden.',
          [
            new JsonFormsHidden('#/properties/_identifier'),
            new JsonFormsControl('#/properties/datei', 'Datei', NULL, ['format' => 'file']),
            new JsonFormsControl('#/properties/beschreibung', 'Beschreibung'),
          ],
          [
            'addButtonLabel' => 'Dokument hinzufügen',
            'removeButtonLabel' => 'Dokument entfernen',
          ]),
      ]),
      ...$preSubmitButtonElements,
      ...$submitButtons,
    ]);
  }

}
