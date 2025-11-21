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
use Civi\RemoteTools\JsonSchema\Util\JsonSchemaUtil;

final class HiHFragenZumProjektJsonSchema extends JsonSchemaObject {

  public function __construct(JsonSchema $ifFullValidation) {
    $abweichendeAnschrift = new JsonSchemaObject([
      'projekttraeger' => new JsonSchemaString(['maxLength' => 255]),
      'strasse' => new JsonSchemaString(['maxLength' => 255]),
      'plz' => new JsonSchemaString(['maxLength' => 255]),
      'ort' => new JsonSchemaString(['maxLength' => 255]),
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
      'projekttraeger' => new JsonSchemaString($minLengthValidation),
      'strasse' => new JsonSchemaString($minLengthValidation),
      'plz' => new JsonSchemaString($minLengthValidation),
      'ort' => new JsonSchemaString($minLengthValidation),
    ], ['required' => ['strasse', 'plz', 'ort']]);

    $properties = [
      'name' => new JsonSchemaString([
        'maxLength' => 255,
        '$tag' => JsonSchema::fromArray(['mapToField' => ['fieldName' => 'title']]),
      ]),
      'ansprechpartner' => new JsonSchemaObject([
        'anrede' => new JsonSchemaString([
          'oneOf' => JsonSchemaUtil::buildTitledOneOf([
            '' => '- Bitte auswählen -',
            'Herr' => 'Herr',
            'Frau' => 'Frau',
            'ohne' => 'Ohne Anrede',
          ]),
        ]),
        'titel' => new JsonSchemaString(['maxLength' => 255]),
        'vorname' => new JsonSchemaString(['maxLength' => 255]),
        'nachname' => new JsonSchemaString(['maxLength' => 255]),
        'telefonnummer' => new JsonSchemaString(['maxLength' => 255]),
        'email' => new JsonSchemaString(['maxLength' => 255, 'format' => 'email']),
      ]),
      'adresseNichtIdentischMitOrganisation' => new JsonSchemaBoolean(['default' => FALSE]),
      'abweichendeAnschrift' => $abweichendeAnschrift,
    ];

    $keywords = [
      'required' => ['name', 'ansprechpartner', 'adresseNichtIdentischMitOrganisation'],
      'if' => $ifFullValidation,
      'then' => JsonSchema::fromArray([
        'properties' => [
          'ansprechpartner' => new JsonSchemaObject([
            'anrede' => new JsonSchemaString($minLengthValidation),
            'vorname' => new JsonSchemaString($minLengthValidation),
            'nachname' => new JsonSchemaString($minLengthValidation),
            'telefonnummer' => new JsonSchemaString($minLengthValidation),
            'email' => new JsonSchemaString($minLengthValidation),
          ], ['required' => ['anrede', 'vorname', 'nachname', 'telefonnummer', 'email']]),
        ],
        'if' => JsonSchema::fromArray([
          'properties' => [
            'adresseNichtIdentischMitOrganisation' => ['const' => TRUE],
          ],
        ]),
        'then' => new JsonSchemaObject([
          'abweichendeAnschrift' => $abweichendeAnschriftRequired,
        ], ['required' => ['abweichendeAnschrift']]),
      ]),
    ];

    parent::__construct($properties, $keywords);
  }

}
