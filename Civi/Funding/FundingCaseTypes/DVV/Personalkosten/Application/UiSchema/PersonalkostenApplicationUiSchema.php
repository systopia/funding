<?php
/*
 * Copyright (C) 2026 SYSTOPIA GmbH
 *
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License as published by the Free
 * Software Foundation, either version 3 of the License, or (at your option) any
 * later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types = 1);

namespace Civi\Funding\FundingCaseTypes\DVV\Personalkosten\Application\UiSchema;

use Civi\RemoteTools\JsonForms\Control\JsonFormsHidden;
use Civi\RemoteTools\JsonForms\JsonFormsControl;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsCategorization;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsCategory;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsGroup;

final class PersonalkostenApplicationUiSchema extends JsonFormsGroup {

  public const FLAG_SHOW_RECIPIENTS_CONTROL = 1;

  public function __construct(string $currency, int $flags) {
    $elements = [];

    $categories = [
      new PersonalkostenGrunddatenUiSchema('#/properties', $currency),
    ];

    if (0 !== ($flags & self::FLAG_SHOW_RECIPIENTS_CONTROL)) {
      $categories[] = new JsonFormsCategory('Antragstellende Organisation', [
        new JsonFormsControl('#/properties/empfaenger', ''),
      ]);
    }
    else {
      $elements[] = new JsonFormsHidden('#/properties/empfaenger');
    }

    $categories[] = new PersonalkostenDokumenteUiSchema();

    $elements[] = new JsonFormsCategorization($categories);

    parent::__construct('Personalkostenförderung', $elements);
  }

}
