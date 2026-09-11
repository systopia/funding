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

namespace Civi\Funding\FundingCaseTypes\DVV\Kurs\Application\UISchema;

use Civi\RemoteTools\JsonForms\Control\JsonFormsArray;
use Civi\RemoteTools\JsonForms\Control\JsonFormsHidden;
use Civi\RemoteTools\JsonForms\JsonFormsControl;
use Civi\RemoteTools\JsonForms\JsonFormsRule;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsCategorization;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsCategory;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsGroup;
use Civi\RemoteTools\JsonSchema\JsonSchema;

final class KursUiSchema extends JsonFormsGroup {

  public const FLAG_SHOW_RECIPIENTS_CONTROL = 1;

  /**
   * @phpstan-param array<int, \Civi\RemoteTools\JsonForms\JsonFormsElement> $extraElements
   */
  public function __construct(string $currency, int $flags, array $extraElements = []) {
    $elements = [];
    $categories = [new KursGrunddatenUiSchema('#/properties/grunddaten/properties')];

    if (0 !== ($flags & self::FLAG_SHOW_RECIPIENTS_CONTROL)) {
      $categories[] = new JsonFormsCategory('Antragstellende Organisation', [
        new JsonFormsControl('#/properties/empfaenger', ''),
      ]);
    }
    else {
      $elements[] = new JsonFormsHidden('#/properties/empfaenger');
    }

    $categories = [...$categories,
      new JsonFormsCategory('Kosten und Finanzierung', [
        // Abschnitt I
        new JsonFormsGroup('Kosten', [
          // Abschnitt I.1
          new JsonFormsGroup('Unterkunft und Verpflegung', [
            new JsonFormsControl(
              '#/properties/kosten/properties/unterkunftUndVerpflegung', 'Unterkunft und Verpflegung in ' . $currency
            ),
          ], 'Hier können Sie die Kosten für Unterbringung und Verpflegung angeben.'),
          // Abschnitt I.2
          new KursHonorareUiSchema($currency),
          // Abschnitt I.4
          new KursFahrtkostenUiSchema($currency),
          // Abschnitt I.5
          new KursSachkostenUiSchema($currency),
          // Abschnitt I.6
          new KursSonstigeAusgabenUiSchema($currency),
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
          new JsonFormsGroup('Spenden', [
            new JsonFormsControl(
              '#/properties/finanzierung/properties/spenden', 'Spenden in ' . $currency
            ),
          ], 'Bitte geben Sie hier die Spenden an, die Sie für Ihr Vorhaben aufbringen können.'),
          // Abschnitt II.3
          new KursOeffentlicheMittelUiSchema($currency),
          new JsonFormsGroup('Finanzierung und beantragter KJP-Zuschuss', [
            new JsonFormsControl(
              '#/properties/finanzierung/properties/maximalerZuschuss', 'Maximaler KJP-Zuschuss in ' . $currency
            ),
            // Abschnitt II.5
            new JsonFormsControl(
              '#/properties/finanzierung/properties/beantragterZuschuss', 'Beantragter KJP-Zuschuss in ' . $currency
            ),
            new JsonFormsControl(
              '#/properties/finanzierung/properties/gesamtfinanzierung', 'Gesamtfinanzierung in ' . $currency
            ),
          ]),
        ]),
      ]),
      // Beschreibung des Vorhabens (not part of default "AV-K1")
      new JsonFormsCategory('Beschreibung des Vorhabens', [
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
          '#/properties/beschreibung/properties/ziele',
          'Welche Ziele hat die Veranstaltung? (Mehrfachauswahl möglich)'
        ),
        new JsonFormsControl(
          '#/properties/beschreibung/properties/bildungsanteil',
          'Wie hoch ist der Bildungsanteil des Vorhabens in %?',
          <<<'EOD'
  Der KJP fördert nur Seminare mit <strong>überwiegendem</strong> Lehr- und
  Fortbildungscharakter. Nicht förderbar sind beispielsweise Projekte, die
  überwiegend der Erholung und Touristik dienen.
  EOD
        ),
        new JsonFormsControl(
          '#/properties/beschreibung/properties/veranstaltungsort', 'Wo findet die Veranstaltung statt?'
        ),
        new JsonFormsControl(
          '#/properties/beschreibung/properties/mitSchuleKooperiert', 'Wird mit einer Schule kooperiert?'
        ),
        new JsonFormsControl(
          '#/properties/beschreibung/properties/partnerschule',
          'Mit welcher Schule wird kooperiert?',
          NULL,
          NULL,
          [
            'rule' => new JsonFormsRule(
              'SHOW',
              '#/properties/beschreibung/properties/mitSchuleKooperiert',
              JsonSchema::fromArray(['const' => TRUE])
            ),
          ]
        ),
        new JsonFormsControl(
          '#/properties/beschreibung/properties/artDerKooperation',
          'Welcher Art entspricht die Kooperation?',
          NULL,
          NULL,
          [
            'rule' => new JsonFormsRule(
              'SHOW',
              '#/properties/beschreibung/properties/mitSchuleKooperiert',
              JsonSchema::fromArray(['const' => TRUE])
            ),
          ]
        ),
      ]),
      new JsonFormsCategory('Projektunterlagen', [
        new JsonFormsArray(
          '#/properties/projektunterlagen',
          'Dokumente',
          <<<EOD
<p>Hier können Unterlagen wie das geplante Programm mit Zeitangaben oder die
Ausschreibung hochgeladen werden.</p>
Die Unterschriftsseite Antrag ist erforderlich.
EOD,
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
    ];

    $elements[] = new JsonFormsCategorization($categories);

    parent::__construct('Förderantrag für Kurs', [
      ...$elements,
      ...$extraElements,
    ]);
  }

}
