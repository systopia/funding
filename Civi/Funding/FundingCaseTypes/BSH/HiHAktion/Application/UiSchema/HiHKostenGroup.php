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

use Civi\RemoteTools\JsonForms\Control\JsonFormsArray;
use Civi\RemoteTools\JsonForms\Control\JsonFormsHidden;
use Civi\RemoteTools\JsonForms\JsonFormsControl;
use Civi\RemoteTools\JsonForms\JsonFormsRule;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsGroup;
use Civi\RemoteTools\JsonSchema\JsonSchema;

final class HiHKostenGroup extends JsonFormsGroup {

  public function __construct(string $scopePrefix, string $currency) {
    $personalkostenRule = new JsonFormsRule(
      'SHOW',
      "$scopePrefix/personalkostenKeine",
      JsonSchema::fromArray(['const' => FALSE])
    );
    $honorareRule = new JsonFormsRule(
      'SHOW',
      "$scopePrefix/honorareKeine",
      JsonSchema::fromArray(['const' => FALSE])
    );
    $sachkostenRule = new JsonFormsRule(
      'SHOW',
      "$scopePrefix/sachkostenKeine",
      JsonSchema::fromArray(['const' => FALSE])
    );

    parent::__construct('Projektkosten', [
      new JsonFormsControl(
        "$scopePrefix/personalkostenKeine",
        'Ich beantrage keine Personalkosten',
      ),
      new JsonFormsGroup('Personalkosten', [
        new JsonFormsArray("$scopePrefix/personalkosten", '', NULL, [
          new JsonFormsHidden('#/properties/_identifier'),
          new JsonFormsControl('#/properties/posten', 'Posten', NULL, ['placeholder' => 'Titel der Stelle']),
          new JsonFormsControl('#/properties/bruttoMonatlich', "Monatliches Arbeitgeberbrutto in $currency"),
          new JsonFormsControl('#/properties/anzahlMonate', 'Monate (insgesamt)'),
          new JsonFormsControl('#/properties/summe', "Summe in $currency"),
        ], [
          'addButtonLabel' => 'Personalkosten hinzufügen',
          'removeButtonLabel' => 'Entfernen',
        ]),
        new JsonFormsControl("$scopePrefix/personalkostenSumme", "Summe Personalkosten in $currency"),
        new JsonFormsControl(
          "$scopePrefix/personalkostenKommentar",
          'Kommentar zu den Personalkosten',
          <<<EOD
Bitte erklären Sie was die Arbeitnehmer:innen machen, wo sie im Projekt arbeiten
und warum sie wichtig sind.
EOD,
          [
            'multi' => TRUE,
            'descriptionDisplay' => 'before',
          ]
        ),
      ], <<<EOD
Als monatliches Arbeitgeberbrutto geben Sie bitte die monatliche Gesamtsumme
pro Mitarbeiter inklusive aller Steuern an (Arbeitgeberbrutto).<br>
Im Feld Monate geben Sie bitte die Gesamtzahl der Monate für den
beantragten Zeitraum an. Zum Beispiel bei einer Förderung von zwei Jahren sind
es 24 Monate.
EOD,
        ['descriptionDisplay' => 'tooltip'],
        ['rule' => $personalkostenRule]
      ),
      new JsonFormsControl(
        "$scopePrefix/honorareKeine",
        'Ich beantrage keine Honorarkosten',
      ),
      new JsonFormsGroup('Honorare', [
        new JsonFormsArray("$scopePrefix/honorare", '', NULL, [
          new JsonFormsHidden('#/properties/_identifier'),
          new JsonFormsControl('#/properties/posten', 'Posten', NULL, ['placeholder' => 'Titel der Stelle']),
          new JsonFormsControl('#/properties/berechnungsgrundlage', "Berechnungs\u{AD}grundlage"),
          new JsonFormsControl('#/properties/verguetung', "Vergütung pro Stunde/\u{200B}Tag in " . $currency),
          new JsonFormsControl('#/properties/dauer', "Stunden/\u{200B}Tage"),
          new JsonFormsControl('#/properties/summe', 'Summe in ' . $currency),
        ], [
          'addButtonLabel' => 'Honorar hinzufügen',
          'removeButtonLabel' => 'Entfernen',
        ]),
        new JsonFormsControl(
          "$scopePrefix/honorareSumme", 'Summe Honorare in ' . $currency
        ),
        new JsonFormsControl(
          "$scopePrefix/honorareKommentar",
          'Kommentar zu den Honorarkosten',
          <<<EOD
Bitte erklären Sie, was die Honorarkräfte machen, wo sie im Projekt arbeiten und
warum sie wichtig sind.
EOD,
          [
            'multi' => TRUE,
            'descriptionDisplay' => 'before',
          ]
        ),
      ], NULL, NULL, ['rule' => $honorareRule]),
      new JsonFormsControl(
        "$scopePrefix/sachkostenKeine",
        'Ich beantrage keine Sachkosten',
      ),
      new JsonFormsGroup('Sachkosten', [
        new JsonFormsGroup(
          '',
          [
            new JsonFormsControl(
              "$scopePrefix/sachkosten/properties/materialien",
              'Projektbezogene Materialien in ' . $currency,
              'z.B. für Veranstaltungen, Workshops, Verbrauchsmaterial',
              ['descriptionDisplay' => 'before']
            ),
            new JsonFormsControl(
              "$scopePrefix/sachkosten/properties/ehrenamtspauschalen",
              'Ehrenamts-/Übungsleiterpauschalen in ' . $currency
            ),
            new JsonFormsControl(
              "$scopePrefix/sachkosten/properties/verpflegung",
              'Verpflegung/Catering in ' . $currency,
              'z.B. für Teilnehmer:innen von Angeboten',
              ['descriptionDisplay' => 'before']
            ),
            new JsonFormsControl(
              "$scopePrefix/sachkosten/properties/fahrtkosten",
              'Fahrtkosten in ' . $currency,
              'z.B. für Ausflüge',
              ['descriptionDisplay' => 'before']
            ),
            new JsonFormsControl(
              "$scopePrefix/sachkosten/properties/oeffentlichkeitsarbeit",
              'Projektbezogene Öffentlichkeitsarbeit in ' . $currency,
              'z.B. Druckkosten, Anzeigen, Gimmicks, RollUps',
              ['descriptionDisplay' => 'before']
            ),
            new JsonFormsControl(
              "$scopePrefix/sachkosten/properties/investitionen",
              'Projektbezogene Investitionen in ' . $currency,
              'z.B. Möbel, Laptop, Software, Fahrradrikscha',
              ['descriptionDisplay' => 'before']
            ),
            new JsonFormsControl(
              "$scopePrefix/sachkosten/properties/mieten",
              'Projektbezogene Mieten in ' . $currency,
              'z.B. für Veranstaltungen',
              ['descriptionDisplay' => 'before']
            ),
            new JsonFormsArray(
              "$scopePrefix/sachkosten/properties/verwaltungskosten",
              'Projektbezogene Verwaltungs-/Organisationskosten',
              'z.B. Telefonkosten, Bürobedarf oder IT-Support',
              [
                new JsonFormsHidden('#/properties/_identifier'),
                new JsonFormsControl('#/properties/bezeichnung', 'Bezeichnung'),
                new JsonFormsControl('#/properties/summe', "Summe in $currency"),
              ],
              [
                'addButtonLabel' => 'Verwaltungs-/Organisationskosten hinzufügen',
                'removeButtonLabel' => 'Entfernen',
                'descriptionDisplay' => 'before',
              ]
            ),
            new JsonFormsArray(
              "$scopePrefix/sachkosten/properties/sonstige",
              'Sonstige Sachkosten in ' . $currency,
              'z.B. Eintrittsgelder für den Besuch von Veranstaltungen',
              [
                new JsonFormsHidden('#/properties/_identifier'),
                new JsonFormsControl('#/properties/bezeichnung', 'Bezeichnung'),
                new JsonFormsControl('#/properties/summe', "Summe in $currency"),
              ],
              [
                'addButtonLabel' => 'Sonstige hinzufügen',
                'removeButtonLabel' => 'Entfernen',
                'descriptionDisplay' => 'before',
              ]
            ),
            new JsonFormsControl(
              "$scopePrefix/sachkosten/properties/sonstigeSumme",
              "Summe sonstige Sachkosten in $currency"
            ),
            new JsonFormsControl(
              "$scopePrefix/sachkosten/properties/summe",
              "Summe Sachkosten in $currency"
            ),
            new JsonFormsControl(
              "$scopePrefix/sachkostenKommentar",
              'Kommentar zu den Sachkosten',
              <<<EOD
    Bitte erklären Sie die Sachkosten: Warum werden sie benötigt und wie werden sie
    im Projekt verwendet? Bitte gehen Sie auf die einzelnen Unterkategorien ein.
    Erklären Sie besonders die Kategorie „Sonstige Sachkosten“:
    EOD,
              [
                'multi' => TRUE,
                'descriptionDisplay' => 'before',
              ]
            ),
          ],
          NULL,
          NULL,
          ['rule' => $sachkostenRule]
        ),
      ], <<<EOD
Bitte beachten Sie, dass nur konkrete Ausgaben für das Projekt beantragt werden
können. Kosten wie Buchhaltung, allgemeine Personalverwaltung oder
Versicherungen für die Organisation sind nicht erlaubt. Auch Einzelfallhilfen
können nicht beantragt werden. Achten Sie bei allen Ausgaben darauf, sparsam und
wirtschaftlich zu sein.
EOD,
        ['descriptionDisplay' => 'tooltip']
      ),
    ]);
  }

}
