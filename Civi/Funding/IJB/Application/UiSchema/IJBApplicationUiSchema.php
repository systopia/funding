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

namespace Civi\Funding\IJB\Application\UiSchema;

use Civi\RemoteTools\JsonForms\JsonFormsControl;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsCategorization;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsCategory;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsGroup;

final class IJBApplicationUiSchema extends JsonFormsGroup {

  public function __construct(string $currency) {
    $elements = [
      new JsonFormsCategorization([
        new IJBGrunddatenUiSchema('#/properties/grunddaten/properties'),
        new IJBTeilnehmerUiSchema('#/properties/teilnehmer/properties'),
        new JsonFormsCategory('Antragstellende Organisation', [
          new JsonFormsControl('#/properties/empfaenger', ''),
        ]),
        new IJBPartnerorganisationUiSchema(),
        new IJBKostenUndFinanzierungUiSchema($currency),
        new IJBBeschreibungUiSchema(),
        new IJBProjektunterlagenUiSchema(),
      ]),
    ];
    parent::__construct('Förderantrag für Jugend- und Fachkräftebegegnungen', $elements);
  }

}
