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

namespace Civi\Funding\FundingCaseTypes\DVV\IJB\Application\UiSchema;

use Civi\RemoteTools\JsonForms\Control\JsonFormsHidden;
use Civi\RemoteTools\JsonForms\JsonFormsControl;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsCategorization;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsCategory;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsGroup;

final class IJBApplicationUiSchema extends JsonFormsGroup {

  public const FLAG_SHOW_RECIPIENTS_CONTROL = 1;

  public function __construct(string $currency, int $flags) {
    $elements = [];
    $categories = [
      new IJBGrunddatenUiSchema('#/properties/grunddaten/properties'),
      new IJBTeilnehmerUiSchema('#/properties/teilnehmer/properties'),
    ];

    if (0 !== ($flags & self::FLAG_SHOW_RECIPIENTS_CONTROL)) {
      $categories[] = new JsonFormsCategory('Antragstellende Organisation', [
        new JsonFormsControl('#/properties/empfaenger', ''),
      ]);
    }
    else {
      $elements[] = new JsonFormsHidden('#/properties/empfaenger');
    }

    $categories = [...$categories,
      new IJBPartnerorganisationUiSchema(),
      new IJBKostenUndFinanzierungUiSchema($currency),
      new IJBBeschreibungUiSchema(),
      new IJBProjektunterlagenUiSchema(),
    ];

    $elements[] = new JsonFormsCategorization($categories);

    parent::__construct('Förderantrag für Jugend- und Fachkräftebegegnungen', $elements);
  }

}
