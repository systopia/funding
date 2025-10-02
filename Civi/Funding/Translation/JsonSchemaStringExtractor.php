<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
 *
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License as published by the Free
 * Software Foundation, either version 3 of the License, or (at your option) any
 * later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types = 1);

namespace Civi\Funding\Translation;

use Civi\RemoteTools\JsonSchema\JsonSchema;

final class JsonSchemaStringExtractor {

  /**
   * @return array<string, true>
   */
  public function extractStrings(JsonSchema $schema): array {
    $strings = [];
    $this->doExtractStrings($strings, $schema);

    return $strings;
  }

  /**
   * @param array<string, true> $strings
   *
   * @param-out array<string, true> $strings
   *
   * phpcs:disable Generic.Metrics.CyclomaticComplexity.TooHigh
   */
  private function doExtractStrings(array &$strings, JsonSchema $schema): void {
  // phpcs:enable
    $validations = $schema['$validations'];
    if (is_array($validations)) {
      foreach ($validations as $validation) {
        if ($validation instanceof JsonSchema) {
          StringExtractUtil::addStrings($strings, $validation, ['message']);
        }
      }
    }

    $oneOf = $schema['oneOf'];
    if (is_array($oneOf)) {
      foreach ($oneOf as $oneOfItem) {
        if ($oneOfItem instanceof JsonSchema) {
          StringExtractUtil::addStrings($strings, $oneOfItem, ['title']);
        }
      }
    }

    if ($schema['items'] instanceof JsonSchema) {
      $this->doExtractStrings($strings, $schema['items']);
    }

    if ($schema['properties'] instanceof JsonSchema) {
      foreach ($schema['properties'] as $property) {
        if ($property instanceof JsonSchema) {
          $this->doExtractStrings($strings, $property);
        }
      }
    }

    $financePlanItem = $schema['$costItem'] ?? $schema['$costItems']
      ?? $schema['$resourcesItem'] ?? $schema['$resourcesItems'];
    $financePlanItemClearing = $financePlanItem['clearing'] ?? NULL;
    if ($financePlanItemClearing instanceof JsonSchema) {
      StringExtractUtil::addStrings($strings, $financePlanItemClearing, ['itemLabel', 'recipientLabel']);
    }
  }

}
