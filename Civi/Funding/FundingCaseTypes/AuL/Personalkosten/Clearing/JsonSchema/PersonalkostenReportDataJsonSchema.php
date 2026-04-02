<?php
/*
 * Copyright (C) 2026 SYSTOPIA GmbH
 *
 *  This program is free software: you can redistribute it and/or modify it under
 *  the terms of the GNU Affero General Public License as published by the Free
 *  Software Foundation, either version 3 of the License, or (at your option) any
 *  later version.
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

namespace Civi\Funding\FundingCaseTypes\AuL\Personalkosten\Clearing\JsonSchema;

use Civi\RemoteTools\JsonSchema\JsonSchema;
use Civi\RemoteTools\JsonSchema\JsonSchemaArray;
use Civi\RemoteTools\JsonSchema\JsonSchemaDataPointer;
use Civi\RemoteTools\JsonSchema\JsonSchemaDate;
use Civi\RemoteTools\JsonSchema\JsonSchemaMoney;
use Civi\RemoteTools\JsonSchema\JsonSchemaObject;
use Civi\RemoteTools\JsonSchema\JsonSchemaString;

final class PersonalkostenReportDataJsonSchema extends JsonSchemaObject {

  public function __construct(
    \DateTimeInterface $applicationBegin,
    \DateTimeInterface $applicationEnd,
  ) {
    $properties = [
      'reportData' => new JsonSchemaObject([
        'internerBezeichner' => new JsonSchemaString(['readOnly' => TRUE]),
        'name' => new JsonSchemaString([
          'minLength' => 1,
          'maxLength' => 100,
        ]),
        'vorname' => new JsonSchemaString([
          'minLength' => 1,
          'maxLength' => 100,
        ]),
        'tarifUndEingruppierung' => new JsonSchemaString([
          'minLength' => 1,
          'maxLength' => 255,
        ]),
        'beginn' => new JsonSchemaDate([
          'minDate' => $applicationBegin->format('Y-m-d'),
          'maxDate' => $applicationEnd->format('Y-m-d'),
          '$tag' => JsonSchema::fromArray(['mapToField' => ['fieldName' => 'start_date']]),
        ]),
        'ende' => new JsonSchemaDate([
          'minDate' => new JsonSchemaDataPointer('1/beginn', '0000-00-00'),
          'maxDate' => $applicationEnd->format('Y-m-d'),
          '$tag' => JsonSchema::fromArray(['mapToField' => ['fieldName' => 'end_date']]),
        ]),
        'dokumente' => new JsonSchemaArray(
          new JsonSchemaObject(
            [
              '_identifier' => new JsonSchemaString(['readOnly' => TRUE]),
              'datei' => new JsonSchemaString([
                'format' => 'uri',
                '$tag' => 'externalFile',
              ]),
              'beschreibung' => new JsonSchemaString(),
            ],
            ['required' => ['datei', 'beschreibung']]
          )
        ),
      ]),
    ];

    parent::__construct($properties, [
      'required' => array_keys($properties),
      '$limitValidation' => JsonSchema::fromArray([
        'condition' => [
          'evaluate' => [
            'expression' => 'action === "save"',
            'variables' => ['action' => new JsonSchemaDataPointer('/_action', '')],
          ],
        ],
      ]),
    ]);
  }

}
