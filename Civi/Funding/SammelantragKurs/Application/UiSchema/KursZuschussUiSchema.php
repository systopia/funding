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

namespace Civi\Funding\SammelantragKurs\Application\UiSchema;

use Civi\Funding\SammelantragKurs\Application\JsonSchema\KursZuschussJsonSchema;
use Civi\RemoteTools\JsonForms\JsonFormsControl;
use Civi\RemoteTools\JsonForms\JsonFormsMarkup;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsCloseableGroup;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsGroup;

final class KursZuschussUiSchema extends JsonFormsCloseableGroup {

  public function __construct(string $currency) {
    $elements = [
      new JsonFormsMarkup(<<<EOD
Anhand der Veranstaltungsdaten wird hier der maximal mögliche KJP-Zuschuss
berechnet. Ist der benötigte KJP-Zuschuss geringer als der maximal mögliche,
wird lediglich der benötigte Zuschuss für den Antrag übernommen.
EOD
      ),
      new JsonFormsGroup('Teilnehmendenkosten', [
        new JsonFormsMarkup(sprintf(
          'Teilnehmendenfestbetrag: %s %s',
          KursZuschussJsonSchema::TEILNEHMERFESTBETRAG,
          $currency
        )),
        new JsonFormsControl(
          '#/properties/zuschuss/properties/teilnehmerkostenMax', 'Maximaler Zuschuss in ' . $currency,
        ),
        new JsonFormsControl(
          '#/properties/zuschuss/properties/teilnehmerkosten', 'Benötigter Zuschuss in ' . $currency,
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
          'Fahrtkostenfestbetrag: %s %s',
          KursZuschussJsonSchema::FAHRTKOSTENFESTBETRAG,
          $currency
        )),
        new JsonFormsControl('#/properties/zuschuss/properties/fahrtkostenMax', 'Maximaler Zuschuss in ' . $currency),
        new JsonFormsControl('#/properties/zuschuss/properties/fahrtkosten', 'Benötigter Zuschuss in ' . $currency),
      ], <<<EOD
Der Reisekostenfestbetrag für Teilnehmer*innen kann nur geltend gemacht
werden, wenn tatsächlich Reisekosten bei den entsprechenden Teilnehmenden
anfallen. Dieser Festbetrag ist für jede*n Teilnehmer*in nur einmal anwendbar.
EOD
      ),
      new JsonFormsGroup('Honorarkosten', [
        new JsonFormsMarkup(sprintf(
          'Honorarkostenfestbetrag: %s %s',
          KursZuschussJsonSchema::HONORARKOSTENFESTBETRAG,
          $currency
        )),
        new JsonFormsControl('#/properties/zuschuss/properties/honorarkostenMax', 'Maximaler Zuschuss in ' . $currency),
        new JsonFormsControl('#/properties/zuschuss/properties/honorarkosten', 'Benötigter Zuschuss in ' . $currency),
      ], <<<EOD
Für jeden Veranstaltungstag, an dem Honorare an Vortragende
ausgezahlt werden, kann pro vortragender Person ein Festbetrag
gewährt werden.
EOD
      ),
      new JsonFormsGroup('Beantragter Zuschuss', [
        new JsonFormsControl('#/properties/zuschuss/properties/gesamt', 'Beantragte KJP-Mittel gesamt in ' . $currency),
      ]),
    ];

    parent::__construct('Zuschussberechnung', $elements);
  }

}
