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

namespace Civi\Funding\FundingCaseTypes\AuL\SammelantragKurs\Report\UiSchema;

use Civi\Funding\FundingCaseTypes\AuL\SammelantragKurs\Application\JsonSchema\KursZuschussJsonSchema;
use Civi\RemoteTools\JsonForms\JsonFormsControl;
use Civi\RemoteTools\JsonForms\JsonFormsMarkup;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsGroup;

final class KursZuschussGroup extends JsonFormsGroup {

  public function __construct(string $currency, string $scopePrefix) {
    $elements = [
      new JsonFormsMarkup(<<<EOD
<p>Anhand der Veranstaltungsdaten wird hier der maximal mögliche KJP-Zuschuss
berechnet.</p>
<p>Festbeträge können nur gewährt werden, wenn in einer Kostenkategorie für die
jeweiligen Personen tatsächliche Ausgaben entstanden sind und nachgewiesen
werden. Bei Festbeträgen können Mittel, die bei der Abrechnung der einzelnen
Kategorien der Festbeträge eingespart werden, da tatsächliche Ausgaben nicht in
Höhe der Festbeträge gegeben sind, für andere zuwendungsfähige Ausgaben
eingesetzt werden. Beispiel: Wenn kein Honorar entstanden ist, kann kein
Honorarfestbetrag gewährt werden. Wenn bei einem Teilnehmenden Reisekosten
entstanden sind, die unter dem Festbetrag liegen, kann die Differenz auch andere
Kosten ausgleichen.</p>
EOD
      ),
      new JsonFormsGroup('Teilnehmendenkosten', [
        new JsonFormsMarkup(sprintf(
          '<p>Teilnehmendenfestbetrag: %s %s</p>',
          KursZuschussJsonSchema::TEILNEHMERFESTBETRAG,
          $currency
        )),
        new JsonFormsControl(
          "$scopePrefix/teilnehmerkostenMax", 'Maximaler Zuschuss in ' . $currency,
        ),
      ], <<<EOD
An jedem Programmtag kann je Teilnehmer*in der Teilnehmendenfestbetrag
geltend gemacht werden. Der maximale Zuschuss für Teilnehmendenkosten
ergibt sich so aus dem Teilnehmendefestbetrag, multipliziert mit der
Gesamtanzahl der Teilnehmer*innen, multipliziert mit der Anzahl der
Programmtage.
EOD
      ),
      new JsonFormsGroup('Fahrtkosten', [
        new JsonFormsMarkup(sprintf(
          '<p>Fahrtkostenfestbetrag: %s %s</p>',
          KursZuschussJsonSchema::FAHRTKOSTENFESTBETRAG,
          $currency
        )),
        new JsonFormsControl("$scopePrefix/fahrtkostenMax", 'Maximaler Zuschuss in ' . $currency),
      ], <<<EOD
Der Reisekostenfestbetrag für Teilnehmer*innen kann nur geltend gemacht
werden, wenn tatsächlich Reisekosten bei den entsprechenden Teilnehmenden
anfallen. Dieser Festbetrag ist für jede*n Teilnehmer*in nur einmal anwendbar.
EOD
      ),
      new JsonFormsGroup('Honorarkosten', [
        new JsonFormsMarkup(sprintf(
          '<p>Honorarkostenfestbetrag: %s %s</p>',
          KursZuschussJsonSchema::HONORARKOSTENFESTBETRAG,
          $currency
        )),
        new JsonFormsControl("$scopePrefix/honorarkostenMax", 'Maximaler Zuschuss in ' . $currency),
      ], <<<EOD
Für jeden Veranstaltungstag, an dem Honorare an Vortragende
ausgezahlt werden, kann pro vortragender Person ein Festbetrag
gewährt werden.
EOD
      ),
      new JsonFormsGroup('Maximal möglicher Gesamtzuschuss in ' . $currency, [
        new JsonFormsControl("$scopePrefix/gesamtMax", ''),
      ]),
    ];

    parent::__construct('Zuschussberechnung', $elements);
  }

}
