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

use Civi\Funding\Form\JsonForms\JsonFormsControl;
use Civi\Funding\Form\JsonForms\Layout\JsonFormsGroup;

final class AVK1UiSchema extends JsonFormsGroup {

  /**
   * @param string $currency
   * @param array<int, \Civi\Funding\Form\JsonForms\Control\JsonFormsSubmitButton> $submitButtons
   */
  public function __construct(string $currency, array $submitButtons) {
    parent::__construct('Förderantrag für sonstige Aktivität', [
      new JsonFormsControl('#/properties/titel', 'Titel'),
      new JsonFormsControl('#/properties/kurzbezeichnungDesInhalts', 'Kurzbezeichnung des Inhalts'),
      new JsonFormsGroup('Kosten', [
        new JsonFormsGroup('Unterkunft und Verpflegung', [
          new JsonFormsControl(
            '#/properties/kosten/properties/unterkunftUndVerpflegung',
            'Unterkunft und Verpflegung', NULL, NULL, $currency
          ),
        ], 'Hier können Sie die Kosten für Unterbringung und Verpflegung angeben.'),
        new AVK1HonorareUiSchema($currency),
        new AVK1SonstigeAusgabenUiSchema($currency),
        new AVK1FahrtkostenUiSchema($currency),
      ]),
      new JsonFormsGroup('Finanzierung', [
        new JsonFormsGroup('Teilnehmerbeiträge', [
          new JsonFormsControl(
            '#/properties/finanzierung/properties/teilnehmerbeitraege',
            'Teilnehmerbeiträge', NULL, NULL, $currency
          ),
        ], 'Bitte geben Sie an, wie viel durch die Teilnehmerbeiträge eingenommen wird.'),
        new JsonFormsGroup('Eigenmittel', [
          new JsonFormsControl(
            '#/properties/finanzierung/properties/eigenmittel',
            'Eigenmittel', NULL, NULL, $currency
          ),
        ], 'Bitte geben Sie hier die Eigenmittel an, die Sie für Ihr Vorhaben aufbringen können.'),
        new AVK1OeffentlicheMittelUiSchema($currency),
        new AVK1SonstigeMittelUiSchema($currency),
        new JsonFormsControl('#/properties/finanzierung/properties/gesamtmittel',
          'Gesamtmittel', NULL, NULL, $currency),
        new JsonFormsControl('#/properties/finanzierung/properties/beantragterZuschuss',
          'Beantragter Zuschuss', NULL, NULL, $currency),
      ]),
      ...$submitButtons,
    ]);
  }

}
