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

final class StringExtractUtil {

  /**
   * @param array<string, true> $strings
   * @param list<string|list<string>> $paths
   *
   * @param-out array<string, true> $strings
   */
  public static function addStrings(array &$strings, JsonSchema $schema, array $paths): void {
    foreach ($paths as $path) {
      $value = $schema->getKeywordValueAtOrDefault($path, NULL);
      self::doAddStrings($strings, $value);
    }
  }

  /**
   * @param array<string, true> $strings
   *
   * @param-out array<string, true> $strings
   */
  private static function doAddStrings(array &$strings, mixed $value): void {
    if (is_string($value)) {
      $value = trim($value);
      if ('' !== $value && strlen($value) <= 8000) {
        $strings[$value] = TRUE;
      }
    }
    elseif ($value instanceof JsonSchema && is_string($value['text'])) {
      self::doAddStrings($strings, $value['text']);
    }
    elseif (is_array($value)) {
      foreach ($value as $string) {
        if (is_string($string)) {
          self::doAddStrings($strings, $string);
        }
      }
    }
  }

}
