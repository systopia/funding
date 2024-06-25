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

  /**
   * @param bool $report TRUE if used for report.
   */
  public function __construct(bool $report = FALSE) {
    $teilnehmerDeutschlandProperties = [
      'gesamt' => new JsonSchemaInteger(['minimum' => 1]),
      'weiblich' => new JsonSchemaInteger(['minimum' => 0], TRUE),
      'divers' => new JsonSchemaInteger(['minimum' => 0], TRUE),
      'unter27' => new JsonSchemaInteger(['minimum' => 0], TRUE),
      'inJugendhilfeEhrenamtlichTaetig' => new JsonSchemaInteger(['minimum' => 0], TRUE),
      'inJugendhilfeHauptamtlichTaetig' => new JsonSchemaInteger(['minimum' => 0], TRUE),
      'referenten' => new JsonSchemaInteger(['minimum' => 0], TRUE),
    ];
    if ($report) {
      $teilnehmerDeutschlandProperties['mitFahrtkosten'] = new JsonSchemaInteger(['minimum' => 0], TRUE);
    }

    $properties = [
      'deutschland' => new JsonSchemaObject($teilnehmerDeutschlandProperties, ['required' => ['gesamt']]),
      'partnerland' => new JsonSchemaObject([
        'gesamt' => new JsonSchemaInteger(['minimum' => 1]),
        'weiblich' => new JsonSchemaInteger(['minimum' => 0], TRUE),
        'divers' => new JsonSchemaInteger(['minimum' => 0], TRUE),
        'unter27' => new JsonSchemaInteger(['minimum' => 0], TRUE),
        'inJugendhilfeEhrenamtlichTaetig' => new JsonSchemaInteger(['minimum' => 0], TRUE),
        'inJugendhilfeHauptamtlichTaetig' => new JsonSchemaInteger(['minimum' => 0], TRUE),
        'referenten' => new JsonSchemaInteger(['minimum' => 0], TRUE),
      ], ['required' => ['gesamt']]),
      'gesamt' => new JsonSchemaCalculate(
        'number',
          'deutschlandGesamt + partnerlandGesamt',
        [
          'deutschlandGesamt' => new JsonSchemaDataPointer('1/deutschland/gesamt', 0),
          'partnerlandGesamt' => new JsonSchemaDataPointer('1/partnerland/gesamt', 0),
        ]
      ),
      'teilnehmertage' => new JsonSchemaCalculate(
        'number',
        'programmtage * teilnehmerGesamt',
        [
          'programmtage' => new JsonSchemaDataPointer('2/grunddaten/programmtage'),
          'teilnehmerGesamt' => new JsonSchemaDataPointer('1/gesamt'),
        ],
        0
      ),
    ];

    parent::__construct($properties, ['required' => ['deutschland', 'partnerland']]);
  }

  /**
   * In report all fields are required.
   */
  public function withAllFieldsRequired(): self {
    $schema = clone $this;
    $schema->addValidations();

    return $schema;
  }

  private function addValidations(): void {
    $requiredIntegers = [
      'weiblich',
      'divers',
      'unter27',
      'inJugendhilfeEhrenamtlichTaetig',
      'inJugendhilfeHauptamtlichTaetig',
      'referenten',
    ];

    // @phpstan-ignore-next-line
    $this['properties']['deutschland']['required']
    // @phpstan-ignore-next-line
      = array_merge($this['properties']['deutschland']['required'], $requiredIntegers, ['mitFahrtkosten']);
    // @phpstan-ignore-next-line
    $this['properties']['partnerland']['required']
    // @phpstan-ignore-next-line
      = array_merge($this['properties']['partnerland']['required'], $requiredIntegers);

    foreach ($requiredIntegers as $property) {
      // @phpstan-ignore-next-line
      $this['properties']['deutschland']['properties'][$property]['type'] = 'integer';
      // @phpstan-ignore-next-line
      $this['properties']['partnerland']['properties'][$property]['type'] = 'integer';
    }
    // @phpstan-ignore-next-line
    $this['properties']['deutschland']['properties']['mitFahrtkosten']['type'] = 'integer';
  }

}
