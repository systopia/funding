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

use Civi\Funding\ApplicationProcess\JsonSchema\ResourcesItem\JsonSchemaResourcesItem;
use Civi\RemoteTools\JsonSchema\JsonSchema;
use Civi\RemoteTools\JsonSchema\JsonSchemaBoolean;
use Civi\RemoteTools\JsonSchema\JsonSchemaCalculate;
use Civi\RemoteTools\JsonSchema\JsonSchemaDataPointer;
use Civi\RemoteTools\JsonSchema\JsonSchemaMoney;
use Civi\RemoteTools\JsonSchema\JsonSchemaObject;
use Civi\RemoteTools\JsonSchema\JsonSchemaString;

final class HiHEinnahmenJsonSchema extends JsonSchemaObject {

  public function __construct() {
    parent::__construct([
      'antragssumme' => new JsonSchemaMoney([
        'minimum' => 0,
        '$resourcesItem' => new JsonSchemaResourcesItem([
          'type' => 'antragssumme',
          'identifier' => 'antragssumme',
          '$tag' => JsonSchema::fromArray(['mapToField' => ['fieldName' => 'amount_requested']]),
        ]),
      ]),
      'andereFoerdermittel' => new JsonSchemaMoney([
        'minimum' => 0,
        '$resourcesItem' => new JsonSchemaResourcesItem([
          'type' => 'andereFoerdermittel',
          'identifier' => 'andereFoerdermittel',
          'clearing' => ['itemLabel' => 'Andere Fördermittel'],
        ]),
      ]),
      'eigenmittel' => new JsonSchemaMoney([
        'minimum' => 0,
        '$resourcesItem' => new JsonSchemaResourcesItem([
          'type' => 'eigenmittel',
          'identifier' => 'eigenmittel',
          'clearing' => ['itemLabel' => 'Eigenmittel'],
        ]),
      ]),
      'gesamteinnahmen' => new JsonSchemaCalculate(
        'number',
        'round(antragssumme + andereFoerdermittel + eigenmittel, 2)',
        [
          'antragssumme' => new JsonSchemaDataPointer('1/antragssumme', 0),
          'andereFoerdermittel' => new JsonSchemaDataPointer('1/andereFoerdermittel', 0),
          'eigenmittel' => new JsonSchemaDataPointer('1/eigenmittel', 0),
        ]
      ),
      'einnahmenKostenDifferenz' => new JsonSchemaCalculate(
        'number',
        'round(gesamteinnahmen - gesamtkosten, 2)',
        [
          'gesamteinnahmen' => new JsonSchemaDataPointer('1/gesamteinnahmen'),
          'gesamtkosten' => new JsonSchemaDataPointer('2/kosten/gesamtkosten'),
        ],
        NULL,
        [
          '$validations' => JsonSchema::convertToJsonSchemaArray([
            [
              'keyword' => 'const',
              'value' => 0,
              'message' => 'Die Finanzierung ist nicht ausgeglichen.',
            ],
          ]),
        ]
      ),
      'einnahmenKommentar' => new JsonSchemaString(['maxLength' => 4000]),
      'kannStattfindenOhneVollstaendigeEinnahmen' => new JsonSchemaBoolean(['const' => TRUE, 'default' => FALSE]),
    ], [
      'required' => [
        'antragssumme',
        'andereFoerdermittel',
        'eigenmittel',
        'kannStattfindenOhneVollstaendigeEinnahmen',
      ],
    ]);
  }

}
