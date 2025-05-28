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

namespace Civi\Funding\FundingCaseTypes\AuL\IJB\Application\UiSchema;

use Civi\RemoteTools\JsonForms\Control\JsonFormsArray;
use Civi\RemoteTools\JsonForms\Control\JsonFormsHidden;
use Civi\RemoteTools\JsonForms\JsonFormsControl;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsGroup;

final class IJBFinanzierungUiSchema extends JsonFormsGroup {

  public function __construct(string $currency) {
    parent::__construct('Finanzierung', [
      new JsonFormsControl(
        '#/properties/finanzierung/properties/teilnehmerbeitraege',
        'Teilnehmer*innenbeiträge in ' . $currency,
        'Bitte geben Sie an, wie viel durch die Teilnehmer*innenbeiträge eingenommen wird.'
      ),
      new JsonFormsControl(
        '#/properties/finanzierung/properties/eigenmittel',
        'Eigenmittel in ' . $currency,
        'Bitte geben Sie hier die Eigenmittel an, die Sie für Ihr Vorhaben aufbringen können.'
      ),
      new JsonFormsGroup('Öffentliche Mittel', [
        new JsonFormsControl(
          '#/properties/finanzierung/properties/oeffentlicheMittel/properties/europa',
          'Finanzierung durch Europa-Mittel in ' . $currency,
        ),
        new JsonFormsControl(
          '#/properties/finanzierung/properties/oeffentlicheMittel/properties/bundeslaender',
          'Finanzierung durch Bundesländer in ' . $currency,
        ),
        new JsonFormsControl(
          '#/properties/finanzierung/properties/oeffentlicheMittel/properties/staedteUndKreise',
          'Finanzierung durch Städte und Kreise in ' . $currency,
        ),
      ], 'Bitte geben Sie weitere Finanzierungen an.'),
      new JsonFormsGroup(
        'Sonstige Mittel',
        [
          new JsonFormsArray('#/properties/finanzierung/properties/sonstigeMittel', '', NULL, [
            new JsonFormsHidden('#/properties/_identifier'),
            new JsonFormsControl('#/properties/quelle', 'Quelle'),
            new JsonFormsControl('#/properties/betrag', 'Betrag in ' . $currency),
          ], [
            'addButtonLabel' => 'Sonstige Mittel hinzufügen',
            'removeButtonLabel' => 'Sonstige Mittel entfernen',
          ]),
          new JsonFormsControl(
            '#/properties/finanzierung/properties/sonstigeMittelGesamt', 'Sonstige Mittel gesamt in ' . $currency
          ),
        ],
        <<<EOD
Bitte geben Sie hier alle weiteren Mittel an, die für das Vorhaben verwendet
werden sollen. Auch Spenden können hier angegeben werden.
EOD
      ),
    ]);
  }

}
