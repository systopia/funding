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

  /**
   * @phpstan-param array<int, \Civi\RemoteTools\JsonForms\Control\JsonFormsSubmitButton> $submitButtons
   */
  public function __construct(string $currency, array $submitButtons) {
    $elements = [
      new JsonFormsCategorization([
        new IJBGrunddatenUiSchema(),
        new IJBTeilnehmerUiSchema(),
        new JsonFormsCategory('Antragstellende Organisation', [
          new JsonFormsControl('#/properties/empfaenger', ''),
        ]),
        new IJBPartnerorganisationUiSchema(),
        new IJBKostenUndFinanzierungUiSchema($currency),
        new IJBBeschreibungUiSchema(),
        new IJBProjektunterlagenUiSchema(),
      ]),
      ...$submitButtons,
    ];
    parent::__construct('Förderantrag für Jugend- und Fachkräftebegegnungen', $elements);
  }

}
