<?php
/*
 * Copyright (C) 2023 SYSTOPIA GmbH
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

namespace Civi\Funding\SonstigeAktivitaet\Application\JsonSchema;

use Civi\RemoteTools\JsonSchema\JsonSchema;
use Civi\RemoteTools\JsonSchema\JsonSchemaArray;
use Civi\RemoteTools\JsonSchema\JsonSchemaDataPointer;
use Civi\RemoteTools\JsonSchema\JsonSchemaDate;
use Civi\RemoteTools\JsonSchema\JsonSchemaObject;
use Civi\RemoteTools\JsonSchema\JsonSchemaString;

final class AVK1GrunddatenSchema extends JsonSchemaObject {

  public function __construct(\DateTimeInterface $applicationBegin, \DateTimeInterface $applicationEnd) {
    $properties = [
      'titel' => new JsonSchemaString(),
      'kurzbeschreibungDesInhalts' => new JsonSchemaString(['maxLength' => 500]),
      'zeitraeume' => new JsonSchemaArray(
        new JsonSchemaObject([
          'beginn' => new JsonSchemaDate([
            'minDate' => $applicationBegin->format('Y-m-d'),
            'maxDate' => $applicationEnd->format('Y-m-d'),
          ]),
          'ende' => new JsonSchemaDate([
            'minDate' => new JsonSchemaDataPointer('1/beginn', '0000-00-00'),
            'maxDate' => $applicationEnd->format('Y-m-d'),
          ]),
        ],
          [
            'required' => ['beginn', 'ende'],
          ]),
        ['minItems' => 1]
      ),
      'teilnehmer' => new JsonSchemaObject([
        'gesamt' => new JsonSchema(['type' => ['integer', 'null'], 'minimum' => 1]),
        'weiblich' => new JsonSchema(['type' => ['integer', 'null'], 'minimum' => 0]),
        'divers' => new JsonSchema(['type' => ['integer', 'null'], 'minimum' => 0]),
        'unter27' => new JsonSchema(['type' => ['integer', 'null'], 'minimum' => 0]),
        'inJugendhilfeTaetig' => new JsonSchema(['type' => ['integer', 'null'], 'minimum' => 0]),
        'referenten' => new JsonSchema(['type' => ['integer', 'null'], 'minimum' => 0]),
      ]),
    ];

    parent::__construct($properties, ['required' => array_keys($properties)]);
  }

}
