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

use Civi\Funding\IJB\Application\JsonSchema\IJBZuschussJsonSchema;
use Civi\RemoteTools\JsonForms\JsonFormsControl;
use Civi\RemoteTools\JsonForms\JsonFormsMarkup;
use Civi\RemoteTools\JsonForms\JsonFormsRule;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsGroup;
use Civi\RemoteTools\JsonSchema\JsonSchema;

final class IJBZuschussUiSchema extends JsonFormsGroup {

  public function __construct(string $currency, string $zuschussScopePrefix, string $grunddatenScopePrefix) {
    $deutschlandRule = new JsonFormsRule(
      'SHOW',
      "$grunddatenScopePrefix/begegnungsland",
      JsonSchema::fromArray(['const' => 'deutschland'])
    );
    $partnerlandRule = new JsonFormsRule(
      'SHOW',
      "$grunddatenScopePrefix/begegnungsland",
      JsonSchema::fromArray(['const' => 'partnerland'])
    );

    $elements = [
      new JsonFormsMarkup(<<<EOD
<p>Anhand der Veranstaltungsdaten wird hier der maximal mögliche KJP-Zuschuss
berechnet. Ist der benötigte KJP-Zuschuss geringer als der maximal mögliche,
wird lediglich der benötigte Zuschuss für den Antrag übernommen.</p>
EOD
      ),
      new JsonFormsGroup('Teilnehmendenkosten', [
        new JsonFormsMarkup(
          sprintf(<<<EOD
<p>Teilnehmendenfestbetrag Fachkräfte im Inland: %s $currency<br>
Teilnehmendenfestbetrag Jugendliche im Inland: %s $currency</p>
EOD,
            IJBZuschussJsonSchema::TEILNEHMER_FESTBETRAG_FACHKRAEFTE,
            IJBZuschussJsonSchema::TEILNEHMER_FESTBETRAG_JUGENDLICHE,
          ),
          'text/html',
          ['rule' => $deutschlandRule]
        ),
        new JsonFormsControl(
          "$zuschussScopePrefix/teilnehmerkostenMax",
          'Maximaler Zuschuss in ' . $currency,
          NULL,
          NULL,
          ['rule' => $deutschlandRule]
        ),
        new JsonFormsControl(
          "$zuschussScopePrefix/teilnehmerkosten",
          'Benötigter Zuschuss in ' . $currency,
          NULL,
          NULL,
          ['rule' => $deutschlandRule]
        ),
      ], <<<EOD
Die Anzahl der Teilnehmendentage ergibt sich aus der Anzahl der Teilnehmer*innen
multipliziert mit der Anzahl der Veranstaltungstage. Dauert die Veranstaltung 4
Tage und es sind 12 Teilnehmer*innen geplant, ergibt sich somit ein Wert von 48
Teilnehmendentagen. Für Fachkräfte gibt es bei Veranstaltungen im Inland den
vollen Tagessatz. Für Kinder- und Jugendliche nur 60 %. Für Maßnahmen im Ausland
gibt es keine Tagessätze.
EOD
      ),
      new JsonFormsGroup('Honorarkosten', [
        new JsonFormsMarkup(
          sprintf(
            '<p>Honorarkostenfestbetrag: %s %s</p>',
            IJBZuschussJsonSchema::HONORARKOSTEN_FESTBETRAG,
            $currency,
          ),
          'text/html',
          ['rule' => $deutschlandRule]
        ),
        new JsonFormsControl(
          "$zuschussScopePrefix/honorarkostenMax",
          'Maximaler Zuschuss in ' . $currency,
        NULL,
        NULL,
          ['rule' => $deutschlandRule]
        ),
        new JsonFormsControl(
          "$zuschussScopePrefix/honorarkosten",
          'Benötigter Zuschuss in ' . $currency,
        NULL,
        NULL,
          ['rule' => $deutschlandRule]
        ),
      ], sprintf(<<<EOD
Für jeden Veranstaltungstag, an dem bei Maßnahmen im Inland Honorare für
Sprachmittlung/Dolmetschung ausgezahlt wird, kann ein Festbetrag von %s $currency
gewährt werden.
EOD,
        IJBZuschussJsonSchema::HONORARKOSTEN_FESTBETRAG)
      ),
      new JsonFormsGroup('Fahrtkosten', [
        new JsonFormsMarkup(
          sprintf(<<<EOD
<p>Fahrtkostenfestbetrag ins europäische Ausland: %s $currency<br>
Fahrtkostenfestbetrag ins außereuropäische Ausland: %s $currency</p>
EOD,
            IJBZuschussJsonSchema::FAHRTKOSTEN_FESTBETRAG_AUSLAND_EUROPA,
            IJBZuschussJsonSchema::FAHRTKOSTEN_FESTBETRAG_NICHT_EUROPA,
          ),
          'text/html',
          ['rule' => $partnerlandRule]
        ),
        new JsonFormsControl(
          "$zuschussScopePrefix/fahrtkostenAuslandEuropaMax",
          'Maximaler Zuschuss im europäischen Ausland in ' . $currency,
          NULL,
          NULL,
          ['rule' => $partnerlandRule]
        ),
        new JsonFormsControl(
          "$zuschussScopePrefix/fahrtkostenNichtEuropaMax",
          'Maximaler Zuschuss im außereuropäischen Ausland in ' . $currency,
          NULL,
          NULL,
          ['rule' => $partnerlandRule]
        ),
        new JsonFormsControl(
          "$zuschussScopePrefix/fahrtkosten",
          'Benötigter Zuschuss in ' . $currency,
          NULL,
        NULL,
          ['rule' => $partnerlandRule]
        ),
      ], sprintf(<<<EOD
Für Reisekosten ab dem Sammel- bzw. Heimatort zum Zielort kann bei Maßnahmen im
Ausland eine Reisekostenpauschale beantragt werden. Dieser Festbetrag ist für
jede(n) Teilnehmer/in pro Kilometer nur einmal anwendbar. Bei Reisen innerhalb
Europas beträgt der Festbetrag %s $currency (km abrunden; Wegstrecke bspw. mit
OpenStreetMap berechnen), bei außereuropäischen Reisen %s $currency (km
abrunden; Luftlinie bspw. mit www.luftlinie.org berechnen).
EOD,
        str_replace('.', ',', (string) IJBZuschussJsonSchema::FAHRTKOSTEN_FESTBETRAG_AUSLAND_EUROPA),
        str_replace('.', ',', (string) IJBZuschussJsonSchema::FAHRTKOSTEN_FESTBETRAG_NICHT_EUROPA),
      )),
      new JsonFormsGroup(
        'Zuschlag für Vor- und Nachbereitung der Maßnahme, Qualifizierung und Auswertung',
        [
          new JsonFormsControl(
            "$zuschussScopePrefix/zuschlagMax",
            'Maximaler Zuschuss in ' . $currency,
          NULL,
          NULL,
            ['rule' => $partnerlandRule]
          ),
          new JsonFormsControl(
            "$zuschussScopePrefix/zuschlag",
            'Benötigter Zuschuss in ' . $currency,
            NULL,
            NULL,
            ['rule' => $partnerlandRule]
          ),
        ],
        sprintf(<<<EOD
<p>Für Fachkräftemaßnahmen im Ausland kann ein Zuschlag in Höhe von %s $currency
pro TN, bis max. %s $currency pro Maßnahme beantragt werden.</p>
<p>Für Jugendbegegnungen im Ausland kann ein Zuschlag in Höhe von %s $currency
pro TN, bis max. %s $currency pro Maßnahme beantragt werden.</p>
EOD,
          IJBZuschussJsonSchema::ZUSCHLAG_FESTBETRAG_FACHKRAEFTE,
          IJBZuschussJsonSchema::ZUSCHLAG_MAX_FACHKRAEFTE,
          IJBZuschussJsonSchema::ZUSCHLAG_FESTBETRAG_JUGENDLICHE,
          IJBZuschussJsonSchema::ZUSCHLAG_MAX_JUGENDLICHE,
        )
      ),
      new JsonFormsGroup('Maximal möglicher Gesamtzuschuss in ' . $currency, [
        new JsonFormsControl("$zuschussScopePrefix/gesamtMax", ''),
      ]),
      new JsonFormsGroup('Beantragter Zuschuss', [
        new JsonFormsControl("$zuschussScopePrefix/gesamt", 'Beantragte KJP-Mittel gesamt in ' . $currency),
      ]),
      new JsonFormsGroup('Gesamtfinanzierung', [
        new JsonFormsControl(
          "$zuschussScopePrefix/finanzierungGesamt",
          'Gesamtfinanzierung in ' . $currency
        ),
      ]),
    ];

    parent::__construct('Zuschussberechnung', $elements);
  }

}
