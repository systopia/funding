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

final class HiHFinanzierungJsonSchema extends JsonSchemaObject {

  public function __construct(JsonSchema $ifFullValidation) {
    $properties = [
      'grundsaetzlich' => new JsonSchemaString(['maxLength' => 255]),
      'gesamtesProjektHiH' => new JsonSchemaBoolean(),
      'wichtigstePositionenBeiTeilbetrag' => new JsonSchemaString(['maxLength' => 255]),
      'andereKosten' => new JsonSchemaString(['maxLength' => 255]),
      'finanzierungZusaetzlicheKosten' => new JsonSchemaString(['maxLength' => 255]),
    ];

    $minLengthValidation = [
      '$validations' => [
        JsonSchema::fromArray([
          'keyword' => 'minLength',
          'value' => 1,
          'message' => 'Dieser Wert ist erforderlich.',
        ]),
      ],
    ];

    $keywords = [
      'if' => $ifFullValidation,
      'then' => JsonSchema::fromArray([
        'properties' => [
          'grundsaetzlich' => new JsonSchemaString($minLengthValidation),
          'wichtigstePositionenBeiTeilbetrag' => new JsonSchemaString($minLengthValidation),
        ],
        'required' => ['grundsaetzlich', 'gesamtesProjektHiH', 'wichtigstePositionenBeiTeilbetrag'],
        'allOf' => [
          JsonSchema::fromArray([
            'if' => [
              'properties' => [
                'gesamtesProjektHiH' => ['const' => FALSE],
              ],
            ],
            'then' => new JsonSchemaObject([
              'andereKosten' => new JsonSchemaString($minLengthValidation),
              'finanzierungZusaetzlicheKosten' => new JsonSchemaString($minLengthValidation),
            ], ['required' => ['andereKosten', 'finanzierungZusaetzlicheKosten']]),
          ]),
        ],
      ]),
    ];

    parent::__construct($properties, $keywords);
  }

}
