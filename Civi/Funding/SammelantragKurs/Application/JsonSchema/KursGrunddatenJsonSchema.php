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

namespace Civi\Funding\SammelantragKurs\Application\JsonSchema;

use Civi\RemoteTools\JsonSchema\JsonSchemaArray;
use Civi\RemoteTools\JsonSchema\JsonSchemaCalculate;
use Civi\RemoteTools\JsonSchema\JsonSchemaDataPointer;
use Civi\RemoteTools\JsonSchema\JsonSchemaDate;
use Civi\RemoteTools\JsonSchema\JsonSchemaInteger;
use Civi\RemoteTools\JsonSchema\JsonSchemaObject;
use Civi\RemoteTools\JsonSchema\JsonSchemaString;

final class KursGrunddatenJsonSchema extends JsonSchemaObject {

  public function __construct(\DateTimeInterface $applicationBegin, \DateTimeInterface $applicationEnd) {
    $properties = [
      'titel' => new JsonSchemaString(),
      'kurzbeschreibungDerInhalte' => new JsonSchemaString(['maxLength' => 500]),
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
      'programmtage' => new JsonSchemaCalculate(
        'number',
        'sum(map(zeitraeume, "date_create(value.beginn).diff(date_create(value.ende)).days + 1"))',
        ['zeitraeume' => new JsonSchemaDataPointer('1/zeitraeume')],
        0
      ),
      'teilnehmer' => new JsonSchemaObject([
        'gesamt' => new JsonSchemaInteger(['minimum' => 1]),
        'weiblich' => new JsonSchemaInteger(['minimum' => 0], TRUE),
        'divers' => new JsonSchemaInteger(['minimum' => 0], TRUE),
        'unter27' => new JsonSchemaInteger(['minimum' => 0], TRUE),
        'inJugendhilfeTaetig' => new JsonSchemaInteger(['minimum' => 0], TRUE),
        'referenten' => new JsonSchemaInteger(['minimum' => 0]),
      ], ['required' => ['gesamt', 'referenten']]),
      'teilnehmertage' => new JsonSchemaCalculate(
        'number',
        'programmtage * teilnehmerGesamt',
        [
          'programmtage' => new JsonSchemaDataPointer('1/programmtage'),
          'teilnehmerGesamt' => new JsonSchemaDataPointer('1/teilnehmer/gesamt'),
        ],
        0
      ),
    ];

    parent::__construct($properties, ['required' => array_keys($properties)]);
  }

}
