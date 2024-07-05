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

namespace Civi\Funding\FundingCaseTypes\BSH\HiHAktion\Application\JsonSchema;

use Civi\RemoteTools\JsonSchema\JsonSchemaBoolean;
use Civi\RemoteTools\JsonSchema\JsonSchemaObject;
use Civi\RemoteTools\JsonSchema\JsonSchemaString;

final class HiHFragenZumProjektJsonSchema extends JsonSchemaObject {

  public function __construct() {
    $properties = [
      'ansprechpartner' => new JsonSchemaString(),
      'anrede' => new JsonSchemaString(),
      'titel' => new JsonSchemaString(),
      'vorname' => new JsonSchemaString(),
      'nachname' => new JsonSchemaString(),
      'adresseIdentischMitOrganisation' => new JsonSchemaBoolean(),
      'abweichendeAnschrift' => new JsonSchemaObject([
        'strasse' => new JsonSchemaString(),
        'plz' => new JsonSchemaString(),
        'ort' => new JsonSchemaString(),
      ]),
      'telefonnummer' => new JsonSchemaString(),
      'email' => new JsonSchemaString(),
    ];

    $keywords = [
      'required' => [
        'ansprechpartner',
        'anrede',
        'vorname',
        'nachname',
        'adresseIdentischMitOrganisation',
        'telefonnummer',
        'email',
      ],
    ];

    parent::__construct($properties, $keywords);
  }

}
