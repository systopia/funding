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
use Civi\RemoteTools\Form\JsonForms\Control\JsonFormsHidden;
use Civi\RemoteTools\Form\JsonForms\JsonFormsControl;
use Civi\RemoteTools\Form\JsonForms\Layout\JsonFormsGroup;

final class AVK1HonorareUiSchema extends JsonFormsGroup {

  public function __construct(string $currency) {
    $elements = [
      new JsonFormsArray('#/properties/kosten/properties/honorare', 'Honorare', NULL, [
        new JsonFormsHidden('#/properties/_identifier'),
        new JsonFormsControl('#/properties/stunden', 'Stunden'),
        new JsonFormsControl('#/properties/verguetung', 'Vergütung', NULL, NULL, $currency),
        new JsonFormsControl('#/properties/zweck', 'Zweck'),
        new JsonFormsControl('#/properties/betrag', 'Betrag', NULL, NULL, $currency),
      ]),
      new JsonFormsControl('#/properties/kosten/properties/honorareGesamt', 'Honorarkosten gesamt',
        NULL, NULL, $currency),
    ];

    parent::__construct(
      'Honorare',
      $elements,
      <<<EOD
  Bitte geben Sie die Kosten für Honorare für Referenten an. Bitte geben Sie
  ebenfalls die Qualifikation der Honorarkraft an (Bsp.: Sozialpädagoge,
  Wissenschaftler, Student Politikwissenschaften).
EOD
    );
  }

}
