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

namespace Civi\Funding\FundingCaseTypes\AuL\IJB\Application\UiSchema;

use Civi\RemoteTools\JsonForms\Control\JsonFormsArray;
use Civi\RemoteTools\JsonForms\Control\JsonFormsHidden;
use Civi\RemoteTools\JsonForms\JsonFormsControl;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsGroup;

final class IJBSonstigeKostenUiSchema extends JsonFormsGroup {

  public function __construct(string $currency) {
    parent::__construct('Sonstige Kosten', [
      new JsonFormsArray('#/properties/kosten/properties/sonstigeKosten',
        '',
        NULL,
        [
          new JsonFormsHidden('#/properties/_identifier'),
          new JsonFormsControl('#/properties/gegenstand', 'Gegenstand'),
          new JsonFormsControl('#/properties/betrag', 'Betrag in ' . $currency),
        ], [
          'addButtonLabel' => 'Sonstige Kosten hinzufügen',
          'removeButtonLabel' => 'Sonstige Kosten entfernen',
        ]
      ),
      new JsonFormsControl(
        '#/properties/kosten/properties/sonstigeKostenGesamt', 'Sonstige Kosten gesamt in ' . $currency
      ),
    ], 'Bitte geben Sie hier alle weiteren Kosten an. Bitte führen Sie jede Position einzeln auf.');
  }

}
