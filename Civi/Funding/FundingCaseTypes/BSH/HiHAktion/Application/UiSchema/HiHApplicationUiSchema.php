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

namespace Civi\Funding\FundingCaseTypes\BSH\HiHAktion\Application\UiSchema;

use Civi\RemoteTools\JsonForms\JsonFormsControl;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsCategorization;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsCategory;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsGroup;

final class HiHApplicationUiSchema extends JsonFormsGroup {

  public function __construct(string $currency) {
    $elements = [
      new JsonFormsCategorization([
        new JsonFormsCategory('Allgemein', [
          new JsonFormsControl('#/properties/empfaenger', <<<EOD
Projekte im Rahmen von „Hand in Hand“ können normalerweise nur zusammen mit
einer Bürgerstiftung aus Norddeutschland einen Antrag stellen. Bitte teilen Sie
uns mit, welche Bürgerstiftung für Sie zuständig ist.
EOD
          ),
          new JsonFormsControl(
            '#/properties/mitBuergerstiftungGesprochen',
            'Haben Sie schon mit jemandem in der ausgewählten Bürgerstiftung gesprochen?',
            'Entfällt, wenn es vor Ort keine Bürgerstiftung gibt.',
            ['descriptionDisplay' => 'before']
          ),
          new HiHFragenZumProjektGroup('#/properties/fragenZumProjekt/properties'),
          new HiHInformationenZumProjektGroup('#/properties/informationenZumProjekt/properties'),
        ]),
        new HiHKostenUndFinanzierungCategory('#/properties', $currency),
        new JsonFormsCategory('Rechtliches', [
          new HiHRechtlichesGroup('#/properties/rechtliches/properties'),
        ]),
      ]),
    ];
    parent::__construct(
      'Förderantrag für die NDR-Benefizaktion „Hand in Hand für Norddeutschland“ – aus einsam wird gemeinsam.',
      $elements
    );
  }

}
