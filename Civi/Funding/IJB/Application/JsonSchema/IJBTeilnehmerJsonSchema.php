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

namespace Civi\Funding\IJB\Application\JsonSchema;

use Civi\RemoteTools\JsonSchema\JsonSchemaCalculate;
use Civi\RemoteTools\JsonSchema\JsonSchemaDataPointer;
use Civi\RemoteTools\JsonSchema\JsonSchemaInteger;
use Civi\RemoteTools\JsonSchema\JsonSchemaObject;

final class IJBTeilnehmerJsonSchema extends JsonSchemaObject {

  public function __construct() {
    $properties = [
      'deutschland' => new JsonSchemaObject([
        'gesamt' => new JsonSchemaInteger(['minimum' => 1]),
        'weiblich' => new JsonSchemaInteger(['minimum' => 0], TRUE),
        'divers' => new JsonSchemaInteger(['minimum' => 0], TRUE),
        'unter27' => new JsonSchemaInteger(['minimum' => 0], TRUE),
        'inJugendhilfeTaetig' => new JsonSchemaInteger(['minimum' => 0], TRUE),
        'referenten' => new JsonSchemaInteger(['minimum' => 0], TRUE),
      ], ['required' => ['gesamt']]),
      'partnerland' => new JsonSchemaObject([
        'gesamt' => new JsonSchemaInteger(['minimum' => 1]),
        'weiblich' => new JsonSchemaInteger(['minimum' => 0], TRUE),
        'divers' => new JsonSchemaInteger(['minimum' => 0], TRUE),
        'unter27' => new JsonSchemaInteger(['minimum' => 0], TRUE),
        'inJugendhilfeTaetig' => new JsonSchemaInteger(['minimum' => 0], TRUE),
        'referenten' => new JsonSchemaInteger(['minimum' => 0], TRUE),
      ], ['required' => ['gesamt']]),
      'gesamt' => new JsonSchemaCalculate(
        'number',
          'deutschlandGesamt + partnerlandGesamt',
        [
          'deutschlandGesamt' => new JsonSchemaDataPointer('1/deutschland/gesamt'),
          'partnerlandGesamt' => new JsonSchemaDataPointer('1/partnerland/gesamt'),
        ]
      ),
      'teilnehmertage' => new JsonSchemaCalculate(
        'number',
        'programmtage * teilnehmerGesamt',
        [
          'programmtage' => new JsonSchemaDataPointer('/grunddaten/programmtage'),
          'teilnehmerGesamt' => new JsonSchemaDataPointer('1/gesamt'),
        ],
        0
      ),
    ];

    parent::__construct($properties, ['required' => ['deutschland', 'partnerland']]);
  }

}
