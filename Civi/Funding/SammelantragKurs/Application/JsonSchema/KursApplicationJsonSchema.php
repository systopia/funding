<?php
/*
 * Copyright (C) 2022 SYSTOPIA GmbH
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

use Civi\RemoteTools\JsonSchema\JsonSchemaObject;

final class KursApplicationJsonSchema extends JsonSchemaObject {

  /**
   * @phpstan-param array<string, \Civi\RemoteTools\JsonSchema\JsonSchema> $extraProperties
   */
  public function __construct(
    \DateTimeInterface $applicationBegin,
    \DateTimeInterface $applicationEnd,
    array $extraProperties = []
  ) {
    // @todo Additional validations, e.g. required, length, min, max, ...
    $properties = [
      'grunddaten' => new KursGrunddatenJsonSchema($applicationBegin, $applicationEnd),
      'finanzierung' => new KursFinanzierungJsonSchema(),
      'zuschuss' => new KursZuschussJsonSchema(),
      'beschreibung' => new KursBeschreibungJsonSchema(),
    ];

    parent::__construct($properties + $extraProperties, ['required' => array_keys($properties)]);
  }

}
