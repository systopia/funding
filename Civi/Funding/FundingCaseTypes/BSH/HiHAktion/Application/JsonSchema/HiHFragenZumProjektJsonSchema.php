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

use Civi\RemoteTools\JsonSchema\JsonSchema;
use Civi\RemoteTools\JsonSchema\JsonSchemaBoolean;
use Civi\RemoteTools\JsonSchema\JsonSchemaObject;
use Civi\RemoteTools\JsonSchema\JsonSchemaString;

final class HiHFragenZumProjektJsonSchema extends JsonSchemaObject {

  public function __construct() {
    $abweichendeAnschrift = new JsonSchemaObject([
      'strasse' => new JsonSchemaString(),
      'plz' => new JsonSchemaString(),
      'ort' => new JsonSchemaString(),
    ]);
    $minLengthValidation = [
      '$validations' => [
        JsonSchema::fromArray([
          'keyword' => 'minLength',
          'value' => 1,
          'message' => 'Dieser Wert ist erforderlich.',
        ]),
      ],
    ];
    $abweichendeAnschriftRequired = new JsonSchemaObject([
      'strasse' => new JsonSchemaString($minLengthValidation),
      'plz' => new JsonSchemaString($minLengthValidation),
      'ort' => new JsonSchemaString($minLengthValidation),
    ], ['required' => ['strasse', 'plz', 'ort']]);

    $properties = [
      'name' => new JsonSchemaString([
        '$tag' => JsonSchema::fromArray(['mapToField' => ['fieldName' => 'title']]),
      ]),
      'ansprechpartner' => new JsonSchemaObject([
        'anrede' => new JsonSchemaString(),
        'titel' => new JsonSchemaString(),
        'vorname' => new JsonSchemaString(),
        'nachname' => new JsonSchemaString(),
      ], ['required' => ['anrede', 'vorname', 'nachname']]),
      'adresseIdentischMitOrganisation' => new JsonSchemaBoolean(),
      'abweichendeAnschrift' => $abweichendeAnschrift,
      'telefonnummer' => new JsonSchemaString(),
      'email' => new JsonSchemaString(),
    ];

    $keywords = [
      'required' => [
        'name',
        'ansprechpartner',
        'adresseIdentischMitOrganisation',
        'telefonnummer',
        'email',
      ],
      'if' => JsonSchema::fromArray([
        'properties' => [
          'adresseIdentischMitOrganisation' => ['const' => FALSE],
        ],
      ]),
      'then' => new JsonSchemaObject([
        'abweichendeAnschrift' => $abweichendeAnschriftRequired,
      ], ['required' => ['abweichendeAnschrift']]),
    ];

    parent::__construct($properties, $keywords);
  }

}
