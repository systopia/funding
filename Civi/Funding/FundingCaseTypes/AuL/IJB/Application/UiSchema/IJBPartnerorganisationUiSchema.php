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
use Civi\RemoteTools\JsonForms\JsonFormsMarkup;
use Civi\RemoteTools\JsonForms\JsonFormsRule;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsCategory;
use Civi\RemoteTools\JsonSchema\JsonSchema;

final class IJBPartnerorganisationUiSchema extends JsonFormsCategory {

  public function __construct() {
    $fortsetzungsMassnahmeRule = new JsonFormsRule(
      'SHOW',
      '#/properties/partnerorganisation/properties/fortsetzungsmassnahme',
      JsonSchema::fromArray(['const' => TRUE]),
    );

    parent::__construct('Partnerorganisation', [
      new JsonFormsControl('#/properties/partnerorganisation/properties/name', 'Name'),
      new JsonFormsControl('#/properties/partnerorganisation/properties/adresse', 'Adresse'),
      new JsonFormsControl('#/properties/partnerorganisation/properties/land', 'Land'),
      new JsonFormsControl('#/properties/partnerorganisation/properties/email', 'E-Mail'),
      new JsonFormsControl('#/properties/partnerorganisation/properties/telefon', 'Telefon'),
      new JsonFormsControl('#/properties/partnerorganisation/properties/kontaktperson', 'Kontaktperson'),
      new JsonFormsControl(
        '#/properties/partnerorganisation/properties/konzeptionellNeu',
        'konzeptionell neu',
      ),
      new JsonFormsControl('#/properties/partnerorganisation/properties/fortsetzungsmassnahme', 'Fortsetzungsmaßnahme'),
      new JsonFormsControl(
        '#/properties/partnerorganisation/properties/austauschSeit',
        'Austausch mit dieser Partnerorganisation seit',
        NULL,
        NULL,
        ['rule' => $fortsetzungsMassnahmeRule],
      ),
      new JsonFormsMarkup(
        '<p>Bisherige Begegnungen mit dieser Partnerorganisation in den letzten fünf Jahren (Zeitraum und Ort)</p>',
        'text/html',
        ['rule' => $fortsetzungsMassnahmeRule]
      ),
      new JsonFormsControl(
        '#/properties/partnerorganisation/properties/bisherigeBegegnungenInDeutschland',
        'In Deutschland',
        NULL,
        NULL,
        ['rule' => $fortsetzungsMassnahmeRule],
      ),
      new JsonFormsControl(
        '#/properties/partnerorganisation/properties/bisherigeBegegnungenImPartnerland',
        'Im Partnerland',
        NULL,
        NULL,
        ['rule' => $fortsetzungsMassnahmeRule],
      ),
    ]);
  }

}
