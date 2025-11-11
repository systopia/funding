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

namespace Civi\Funding\FundingCaseTypes\AuL\IJB\Report\UiSchema;

use Civi\RemoteTools\JsonForms\JsonFormsControl;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsGroup;

final class IJBFoerderungGroup extends JsonFormsGroup {

  public function __construct(string $scopePrefix, string $currency) {
    parent::__construct('Verteilung des abgerechneten Betrages auf die Festbeträge', [
      new JsonFormsControl(
        "$scopePrefix/teilnahmetage",
        'KJP-Festbetragsförderung Teilnahmetage in ' . $currency
      ),
      new JsonFormsControl("$scopePrefix/honorare", 'KJP-Festbetragsförderung Honorare in ' . $currency),
      new JsonFormsControl(
        "$scopePrefix/fahrtkosten",
        'KJP-Festbetragsförderung Fahrtkosten in ' . $currency
      ),
      new JsonFormsControl("$scopePrefix/zuschlaege", 'KJP-Festbetragsförderung Zuschläge in ' . $currency),
      new JsonFormsControl("$scopePrefix/summe", 'KJP-Festbetragsförderung gesamt in ' . $currency),
    ]);
  }

}
