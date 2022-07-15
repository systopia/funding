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

final class AVK1OeffentlicheMittelUiSchema extends JsonFormsGroup {

  public function __construct(string $currency) {
    $elements = [
      new JsonFormsGroup('Öffentliche Mittel', [
        new JsonFormsControl('#/properties/finanzierung/properties/oeffentlicheMittel/properties/europa',
          'Europa', NULL, NULL, $currency),
        new JsonFormsControl('#/properties/finanzierung/properties/oeffentlicheMittel/properties/bundeslaender',
          'Bundesländer', NULL, NULL, $currency),
        new JsonFormsControl('#/properties/finanzierung/properties/oeffentlicheMittel/properties/staedteUndKreise',
          'Städte und Kreise', NULL, NULL, $currency),
      ], 'Bitte geben Sie die öffentlichen Mittel an.'),
    ];

    parent::__construct(
      'Öffentliche Mittel',
      $elements,
      'Bitte geben Sie die öffentlichen Mittel an.'
    );
  }

}
