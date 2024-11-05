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
          new JsonFormsControl('#/properties/empfaenger', 'Welche Bürgerstiftung befindet sich in Ihrer Nähe?', <<<EOD
Projekte im Rahmen von „Hand in Hand für Norddeutschland“ können in der Regel
nur gemeinsam mit einer Bürgerstiftung aus Norddeutschland einen Antrag stellen.
Dabei können auch neue Partnerschaften entstehen. Bitte wählen Sie die
Bürgerstiftung in Ihrer Nähe aus oder sagen Sie, wenn es keine gibt. Wenn Sie
Hilfe bei der Suche benötigen, gucken Sie z.B.
<a href="https://www.aktive-buergerschaft.de/buergerstiftungen/buergerstiftung-finden/" target="_blank">hier</a>.
Wir empfehlen, vor der Antragsstellung Kontakt mit der Bürgerstiftung
aufzunehmen.
EOD,
            ['descriptionDisplay' => 'before'],
          ),
          new HiHFragenZumProjektGroup('#/properties/fragenZumProjekt/properties'),
          new HiHInformationenZumProjektGroup('#/properties/informationenZumProjekt/properties'),
        ]),
        new HiHKostenUndFinanzierungCategory('#/properties', $currency),
        new JsonFormsCategory('Formales', [
          new HiHFormalesGroup('#/properties/formales/properties'),
        ]),
      ]),
    ];
    parent::__construct(
      'Förderantrag für die NDR-Benefizaktion „Hand in Hand für Norddeutschland“ – aus einsam wird gemeinsam.',
      $elements
    );
  }

}
