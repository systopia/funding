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

namespace Civi\Funding\FundingCaseTypes\AuL\SonstigeAktivitaet\Report\JsonSchema;

use Civi\RemoteTools\JsonSchema\JsonSchema;
use Civi\RemoteTools\JsonSchema\JsonSchemaDataPointer;
use Civi\RemoteTools\JsonSchema\JsonSchemaObject;
use Civi\RemoteTools\JsonSchema\JsonSchemaString;
use Civi\RemoteTools\JsonSchema\Util\JsonSchemaUtil;

final class AVK1SachberichtJsonSchema extends JsonSchemaObject {

  public function __construct() {
    parent::__construct([
      'durchgefuehrt' => new JsonSchemaString([
        'oneOf' => JsonSchemaUtil::buildTitledOneOf2([
          'entsprechend dem geplanten Programm' => 'geplant',
          'mit folgenden wesentlichen Änderungen (kurze Begründung für die Änderung):' => 'geaendert',
        ]),
      ]),
      'aenderungen' => new JsonSchemaString([
        '$validations' => [
          JsonSchema::fromArray([
            'keyword' => 'evaluate',
            'value' => [
              'expression' => 'data != "" || durchgefuehrt === "geplant"',
              'variables' => [
                'durchgefuehrt' => new JsonSchemaDataPointer('1/durchgefuehrt'),
              ],
            ],
            'message' => 'Bitte Begründung für die Änderungen angeben.',
          ]),
        ],
      ], TRUE),
      'thematischeSchwerpunkte' => new JsonSchemaString(['minLength' => 1]),
      'methoden' => new JsonSchemaString(['minLength' => 1]),
      'zielgruppe' => new JsonSchemaString(['minLength' => 1]),
      'sonstiges' => new JsonSchemaString(['minLength' => 1]),
    ], [
      'required' => [
        'durchgefuehrt',
        'thematischeSchwerpunkte',
        'methoden',
        'zielgruppe',
        'sonstiges',
      ],
    ]);
  }

}
