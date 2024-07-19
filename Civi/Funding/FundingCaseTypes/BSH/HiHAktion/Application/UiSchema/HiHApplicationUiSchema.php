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
use Civi\RemoteTools\JsonForms\Layout\JsonFormsGroup;

final class HiHApplicationUiSchema extends JsonFormsGroup {

  public function __construct(string $currency) {
    $elements = [
      new JsonFormsControl('#/properties/empfaenger', 'Fördergeldempfänger'),
      new HiHFragenZumProjektGroup('#/properties/fragenZumProjekt/properties'),
      new HiHInformationenZumProjektGroup('#/properties/informationenZumProjekt/properties'),
      new HiHKostenUndFinanzierungGroup('#/properties/kostenUndFinanzierung/properties', $currency),
      new HiHRechtlichesGroup('#/properties/rechtliches/properties'),
    ];
    parent::__construct('Förderantrag für NDR-Benefizaktion', $elements);
  }

}
