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

use Civi\RemoteTools\Form\JsonForms\Control\JsonFormsArray;
use Civi\RemoteTools\Form\JsonForms\JsonFormsControl;
use Civi\RemoteTools\Form\JsonForms\Layout\JsonFormsGroup;

final class AVK1SonstigeMittelUiSchema extends JsonFormsGroup {

  public function __construct(string $currency) {
    $elements = [
      new JsonFormsArray('#/properties/finanzierung/properties/sonstigeMittel', 'Sonstige Mittel', NULL, [
        new JsonFormsControl('#/properties/betrag', 'Betrag', NULL, NULL, $currency),
        new JsonFormsControl('#/properties/quelle', 'Quelle'),
      ]),
      new JsonFormsControl('#/properties/finanzierung/properties/sonstigeMittelGesamt',
        'Sonstige Mittel gesamt', NULL, NULL, $currency),
    ];

    parent::__construct(
      'Sonstige Mittel',
      $elements,
      'Bitte geben Sie hier alle Mittel an, die keiner der anderen Kategorien zuzuordnen sind.'
    );
  }

}
