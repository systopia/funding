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

use Civi\RemoteTools\JsonForms\JsonFormsControl;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsGroup;

final class IJBFahrtkostenUiSchema extends JsonFormsGroup {

  public function __construct(string $currency) {
    $elements = [
      new JsonFormsControl(
        '#/properties/kosten/properties/fahrtkosten/properties/flug',
        'Fahrt-/Flugkosten inkl. Transfer zur Unterkunft in ' . $currency,
        'Bitte geben Sie hier die Fahrtkosten an, die innerhalb der
        Veranstaltung anfallen und nicht an Teilnehmende erstattet werden.'
      ),
      new JsonFormsControl(
        '#/properties/kosten/properties/fahrtkosten/properties/anTeilnehmerErstattet',
        'An Teilnehmer*innen erstattete Fahrtkosten in ' . $currency,
        'Bitte geben Sie die Fahrtkosten an, die an Teilnehmer*innen erstattet werden.'
      ),
      new JsonFormsControl(
        '#/properties/kosten/properties/fahrtkostenGesamt', 'Fahrtkosten gesamt in ' . $currency
      ),
    ];

    parent::__construct('Fahrtkosten', $elements);
  }

}
