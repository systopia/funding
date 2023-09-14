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

namespace Civi\Funding\IJB\Application\UiSchema;

use Civi\RemoteTools\JsonForms\Control\JsonFormsArray;
use Civi\RemoteTools\JsonForms\Control\JsonFormsHidden;
use Civi\RemoteTools\JsonForms\JsonFormsControl;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsGroup;

final class IJBHonorareUiSchema extends JsonFormsGroup {

  public function __construct(string $currency) {
    // \u{AD} is used to allow hyphenation at this position.
    // \u{200B} is used to allow line break at this position.
    $elements = [
      new JsonFormsArray('#/properties/kosten/properties/honorare', '', NULL, [
        new JsonFormsHidden('#/properties/_identifier'),
        new JsonFormsControl('#/properties/berechnungsgrundlage', "Berechnungs\u{AD}grundlage"),
        new JsonFormsControl('#/properties/dauer', "Stunden/\u{200B}Tage"),
        new JsonFormsControl('#/properties/verguetung', "Vergütung pro Stunde/\u{200B}Tag in " . $currency),
        new JsonFormsControl('#/properties/leistung', 'Vereinbarte Leistung'),
        new JsonFormsControl(
          '#/properties/qualifikation', 'Qualifikation', <<<EOD
Bitte geben Sie ebenfalls die Qualifikation der Honorarkraft an (Bsp.:
Sozialpädagog*in, Wissenschaftler*in, Student*in Politikwissenschaften).
EOD
        ),
        new JsonFormsControl('#/properties/betrag', 'Betrag in ' . $currency),
      ], [
        'addButtonLabel' => 'Honorar hinzufügen',
        'removeButtonLabel' => 'Entfernen',
      ]),
      new JsonFormsControl(
        '#/properties/kosten/properties/honorareGesamt', 'Honorarkosten gesamt in ' . $currency
      ),
    ];

    parent::__construct(
      'Honorare',
      $elements,
      <<<EOD
Bitte geben Sie die Kosten für Honorare für Referent*innen an. Ebenso können Sie
hier Kosten für Sprachmittlung/Dolmetschung im Inland angeben.
EOD
    );
  }

}
