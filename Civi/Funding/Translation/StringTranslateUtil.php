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

final class StringTranslateUtil {

  /**
   * @param list<string|non-empty-list<string>> $paths
   * @param array<string, string> $translations
   */
  public static function translateStrings(
    JsonSchema $schema,
    array $paths,
    array $translations,
    string $defaultLocale
  ): void {
    foreach ($paths as $path) {
      if (is_string($path)) {
        $path = explode('/', ltrim($path, '/'));
      }

      $lastPathElement = array_pop($path);
      if ([] === $path) {
        self::doTranslate($schema, $lastPathElement, $translations, $defaultLocale);
      }
      else {
        $stringContainingSchema = $schema->getKeywordValueAtOrDefault($path, NULL);
        if ($stringContainingSchema instanceof JsonSchema) {
          self::doTranslate($stringContainingSchema, $lastPathElement, $translations, $defaultLocale);
        }
      }
    }
  }

  /**
   * @param array<string, string> $translations
   */
  private static function doTranslate(
    JsonSchema $schema,
    string $key,
    array $translations,
    string $defaultLocale
  ): void {
    $current = $schema[$key];
    if (is_string($current)) {
      $current = trim($current);
      if (isset($translations[$current])) {
        $schema[$key] = $translations[$current];
      }
    }
    elseif ($current instanceof JsonSchema && is_string($current['text'])) {
      $text = trim($current['text']);
      if (isset($translations[$text])) {
        $text = $translations[$text];
      }
      if (is_string($current['locale'])) {
        $locale = $current['locale'];
      }
      else {
        $locale = $defaultLocale;
      }

      if ($current['values'] instanceof JsonSchema) {
        $schema[$key] = \MessageFormatter::formatMessage($locale, $text, $current['values']->toArray());
      }
      else {
        $schema[$key] = \MessageFormatter::formatMessage($locale, $text, []);
      }
    }
  }

}
