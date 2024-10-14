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
use Civi\RemoteTools\JsonForms\JsonFormsRule;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsGroup;
use Civi\RemoteTools\JsonSchema\JsonSchema;

final class HiHFragenZumProjektGroup extends JsonFormsGroup {

  public function __construct(string $scopePrefix) {
    parent::__construct('Fragen zum Projekt', [
      new JsonFormsControl(
        "$scopePrefix/name",
        'Wie heißt das Projekt?'
      ),
      new JsonFormsGroup('Ansprechpartner:in für das Projekt', [
        new JsonFormsControl(
          "$scopePrefix/ansprechpartner/properties/anrede",
          'Anrede'
        ),
        new JsonFormsControl(
          "$scopePrefix/ansprechpartner/properties/titel",
          'Titel'
        ),
        new JsonFormsControl(
          "$scopePrefix/ansprechpartner/properties/vorname",
          'Vorname'
        ),
        new JsonFormsControl(
          "$scopePrefix/ansprechpartner/properties/nachname",
          'Nachname'
        ),
        new JsonFormsControl(
          "$scopePrefix/ansprechpartner/properties/telefonnummer",
          'Telefonnummer'
        ),
        new JsonFormsControl(
          "$scopePrefix/ansprechpartner/properties/email",
          'E-Mail'
        ),
      ]),
      new JsonFormsControl(
        "$scopePrefix/adresseNichtIdentischMitOrganisation",
        'Ist die Projektadresse abweichend zur Organisation?'
      ),
      new JsonFormsGroup('Abweichende Anschrift Projekt', [
        new JsonFormsControl(
          "$scopePrefix/abweichendeAnschrift/properties/projekttraeger",
          'Name Projektträger'
        ),
        new JsonFormsControl(
          "$scopePrefix/abweichendeAnschrift/properties/strasse",
          'Straße und Hausnummer'
        ),
        new JsonFormsControl(
          "$scopePrefix/abweichendeAnschrift/properties/plz",
          'Postleitzahl'
        ),

        new JsonFormsControl(
          "$scopePrefix/abweichendeAnschrift/properties/ort",
          'Stadt'
        ),
      ], NULL, NULL, [
        'rule' => new JsonFormsRule(
          'HIDE',
          "$scopePrefix/adresseNichtIdentischMitOrganisation",
          JsonSchema::fromArray(['const' => FALSE])
        ),
      ]),
    ]);
  }

}
