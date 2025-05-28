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

namespace Civi\Funding\FundingCaseTypes\AuL\IJB\Report\JsonSchema;

use Civi\RemoteTools\JsonSchema\JsonSchemaCalculate;
use Civi\RemoteTools\JsonSchema\JsonSchemaDataPointer;
use Civi\RemoteTools\JsonSchema\JsonSchemaMoney;
use Civi\RemoteTools\JsonSchema\JsonSchemaObject;

final class IJBFoerderungJsonSchema extends JsonSchemaObject {

  public function __construct() {
    parent::__construct([
      'teilnahmetage' => new JsonSchemaMoney([], TRUE),
      'honorare' => new JsonSchemaMoney([], TRUE),
      'fahrtkosten' => new JsonSchemaMoney([], TRUE),
      'zuschlaege' => new JsonSchemaMoney([], TRUE),
      'summe' => new JsonSchemaCalculate(
        'number',
        'teilnahmetage + honorare + fahrtkosten + zuschlaege',
        [
          'teilnahmetage' => new JsonSchemaDataPointer('1/teilnahmetage', 0),
          'honorare' => new JsonSchemaDataPointer('1/honorare', 0),
          'fahrtkosten' => new JsonSchemaDataPointer('1/fahrtkosten', 0),
          'zuschlaege' => new JsonSchemaDataPointer('1/zuschlaege', 0),
        ]
      ),
    ]);
  }

  public function withAllFieldsRequired(): self {
    $schema = clone $this;
    $schema->addValidations();

    return $schema;
  }

  private function addValidations(): void {
    // @phpstan-ignore-next-line
    $requiredNumbers = array_keys($this['properties']->getKeywords());
    $this['required'] = $requiredNumbers;

    foreach ($requiredNumbers as $property) {
      // @phpstan-ignore-next-line
      $this['properties'][$property]['type'] = 'number';
    }
  }

}
