<?php
/*
 * Copyright (C) 2024 SYSTOPIA GmbH
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

final class HiHEinnahmenGroup extends JsonFormsGroup {

  public function __construct(string $scopePrefix, string $currency) {
    parent::__construct('Einnahmen (Gesamtmittel)', [
      new JsonFormsControl(
        "$scopePrefix/antragssumme",
        'Antragssumme "Hand in Hand" in ' . $currency
      ),
      new JsonFormsControl(
        "$scopePrefix/andereFoerdermittel",
        "Andere Fördermittel (z.B. öffentliche Förderung, Stiftungen usw.) in $currency",
        'Bitte geben Sie hier ggf. auch Mittel an, die sie beantragt haben, die aber noch nicht bewilligt worden sind.'
      ),
      new JsonFormsControl(
        "$scopePrefix/eigenmittel",
        "Eigenmittel (z.B. Spenden usw.) in $currency"
      ),
      new JsonFormsControl(
        "$scopePrefix/gesamteinnahmen",
        "Gesamtsumme Einnahmen in $currency"
      ),
    ]);
  }

}
