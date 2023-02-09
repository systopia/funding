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

use Civi\RemoteTools\Form\JsonForms\JsonFormsControl;
use Civi\RemoteTools\Form\JsonForms\Layout\JsonFormsGroup;

/**
 * This implements the UI schema for an "AV-K1" form to apply for a funding for
 * a "Sonstige Aktivität" in the "Kinder- und Jugendplan des Bundes (KJP)".
 * Because it is a specific German form strings are not translated.
 */
final class AVK1UiSchema extends JsonFormsGroup {

  /**
   * @phpstan-param array<int, \Civi\RemoteTools\Form\JsonForms\Control\JsonFormsSubmitButton> $submitButtons
   * @phpstan-param array<int, \Civi\RemoteTools\Form\JsonForms\Control\JsonFormsHidden> $hiddenFields
   */
  public function __construct(string $currency, array $submitButtons = [], array $hiddenFields = []) {
    parent::__construct('Förderantrag für sonstige Aktivität', [
      new JsonFormsControl('#/properties/file', 'File', NULL, NULL, NULL, ['format' => 'file']),
      new JsonFormsControl('#/properties/titel', 'Titel'),
      new JsonFormsControl('#/properties/kurzbezeichnungDesInhalts', 'Kurzbezeichnung des Inhalts'),
      new JsonFormsControl('#/properties/empfaenger', 'Empfänger'),
      new JsonFormsControl('#/properties/beginn', 'Beginn'),
      new JsonFormsControl('#/properties/ende', 'Ende'),
      // Abschnitt I
      new JsonFormsGroup('Kosten', [
        // Abschnitt I.1
        new JsonFormsGroup('Unterkunft und Verpflegung', [
          new JsonFormsControl(
            '#/properties/kosten/properties/unterkunftUndVerpflegung',
            'Unterkunft und Verpflegung', NULL, NULL, $currency
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
            '#/properties/kosten/properties/versicherungTeilnehmer',
            'Kosten der Versicherung der Teilnehmenden', NULL, NULL, $currency
          ),
        ]),
        new JsonFormsControl('#/properties/kosten/properties/gesamtkosten', 'Gesamtkosten'),
      ]),
      // Abschnitt II
      new JsonFormsGroup('Finanzierung', [
        // Abschnitt II.2
        new JsonFormsGroup('Teilnehmerbeiträge', [
          new JsonFormsControl(
            '#/properties/finanzierung/properties/teilnehmerbeitraege',
            'Teilnehmerbeiträge', NULL, NULL, $currency
          ),
        ], 'Bitte geben Sie an, wie viel durch die Teilnehmerbeiträge eingenommen wird.'),
        // Abschnitt II.2
        new JsonFormsGroup('Eigenmittel', [
          new JsonFormsControl(
            '#/properties/finanzierung/properties/eigenmittel',
            'Eigenmittel', NULL, NULL, $currency
          ),
        ], 'Bitte geben Sie hier die Eigenmittel an, die Sie für Ihr Vorhaben aufbringen können.'),
        // Abschnitt II.3
        new AVK1OeffentlicheMittelUiSchema($currency),
        // Abschnitt II.4
        new AVK1SonstigeMittelUiSchema($currency),
        // Gesamtmittel ohne Zuschuss
        new JsonFormsControl('#/properties/finanzierung/properties/gesamtmittel',
          'Gesamtmittel', NULL, NULL, $currency),
        // Abschnitt II.5
        new JsonFormsControl('#/properties/finanzierung/properties/beantragterZuschuss',
          'Beantragter Zuschuss', NULL, NULL, $currency),
      ]),
      ...$submitButtons,
      ...$hiddenFields,
    ]);
  }

}
