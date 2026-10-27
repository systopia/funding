<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
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

namespace Civi\Funding\FundingCaseTypes\AdB\SammelantragKurs\Application\JsonSchema;

use Civi\RemoteTools\JsonSchema\JsonSchema;
use Civi\RemoteTools\JsonSchema\JsonSchemaBoolean;
use Civi\RemoteTools\JsonSchema\JsonSchemaInteger;
use Civi\RemoteTools\JsonSchema\JsonSchemaObject;

final class KursApplicationJsonSchema extends JsonSchemaObject {

  public function __construct(
    \DateTimeInterface $applicationBegin,
    \DateTimeInterface $applicationEnd,
    ?bool $nichtGefoerdert
  ) {
    $properties = [
      'grunddaten' => new KursGrunddatenJsonSchema($applicationBegin, $applicationEnd),
      'beschreibung' => new KursBeschreibungJsonSchema(),
    ];

    if (TRUE === $nichtGefoerdert) {
      $properties['nichtGefoerdert'] = new JsonSchemaBoolean(['const' => TRUE]);
      // Initially amount_requested comes from JSON schema for "zuschuss".
      $properties['beantragterBetrag'] = new JsonSchemaInteger([
        'const' => 0,
        'default' => 0,
        '$default' => 0,
        '$tag' => JsonSchema::fromArray(['mapToField' => ['fieldName' => 'amount_requested']]),
      ]);
    }
    else {
      if (NULL === $nichtGefoerdert) {
        $properties['nichtGefoerdert'] = new JsonSchemaBoolean();
      }
      else {
        $properties['nichtGefoerdert'] = new JsonSchemaBoolean(['const' => FALSE]);
      }

      $properties['finanzierung'] = new KursFinanzierungJsonSchema();
      // When "nichtGefoerdert" is FALSE on initial submit cost items and resources items are removed by validator.
      $properties['zuschuss'] = new KursZuschussJsonSchema();
    }

    parent::__construct($properties, ['required' => array_keys($properties)]);
  }

}
