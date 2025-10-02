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

final class JsonSchemaStringTranslator {

  /**
   * @param array<string, string> $translations
   *   Mapping of original string to translation.
   *
   * phpcs:disable Generic.Metrics.CyclomaticComplexity.TooHigh
   */
  public function translateStrings(JsonSchema $schema, array $translations, string $defaultLocale): void {
  // phpcs:enable
    $validations = $schema['$validations'];
    if (is_array($validations)) {
      foreach ($validations as $validation) {
        if ($validation instanceof JsonSchema) {
          StringTranslateUtil::translateStrings($validation, ['message'], $translations, $defaultLocale);
        }
      }
    }

    $oneOf = $schema['oneOf'];
    if (is_array($oneOf)) {
      foreach ($oneOf as $oneOfItem) {
        if ($oneOfItem instanceof JsonSchema) {
          StringTranslateUtil::translateStrings($oneOfItem, ['title'], $translations, $defaultLocale);
        }
      }
    }

    if ($schema['items'] instanceof JsonSchema) {
      $this->translateStrings($schema['items'], $translations, $defaultLocale);
    }

    if ($schema['properties'] instanceof JsonSchema) {
      foreach ($schema['properties'] as $property) {
        if ($property instanceof JsonSchema) {
          $this->translateStrings($property, $translations, $defaultLocale);
        }
      }
    }

    $financePlanItem = $schema['$costItem'] ?? $schema['$costItems']
      ?? $schema['$resourcesItem'] ?? $schema['$resourcesItems'];
    $financePlanItemClearing = $financePlanItem['clearing'] ?? NULL;
    if ($financePlanItemClearing instanceof JsonSchema) {
      StringTranslateUtil::translateStrings(
        $financePlanItemClearing,
        ['itemLabel', 'recipientLabel'],
        $translations,
        $defaultLocale
      );
    }
  }

}
