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

namespace Civi\RemoteTools\Form\JsonSchema;

/**
 * Non-standard schema representing a calculated value.
 */
class JsonSchemaCalculate extends JsonSchema {

  /**
   * @param string $type
   * @param string $expression
   * @param array<string, scalar|JsonSchema> $variables
   * @param scalar|null|JsonSchema $fallback
   * @param array<string, scalar|JsonSchema|null> $keywords
   */
  public function __construct(string $type, string $expression, array $variables,
    $fallback = NULL, array $keywords = []
  ) {
    $calculate = [
      'expression' => $expression,
      'variables' => new JsonSchema($variables),
    ];
    if (NULL !== $fallback) {
      $calculate['fallback'] = $fallback;
    }

    parent::__construct([
      'type' => $type,
      '$calculate' => new JsonSchema($calculate),
    ] + $keywords);
  }

}
