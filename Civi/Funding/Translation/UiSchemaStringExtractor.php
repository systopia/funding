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

final class UiSchemaStringExtractor {

  /**
   * @return array<string, true>
   */
  public function extractStrings(JsonSchema $element): array {
    $strings = [];
    $this->doExtractStrings($strings, $element);

    return $strings;
  }

  /**
   * @param array<string, true> $strings
   *
   * @param-out array<string, true> $strings
   */
  private function doExtractStrings(array &$strings, JsonSchema $element): void {
    // "$translatableTexts" may contain a list of strings to translate. This can
    // be used to include strings that are only available under specific
    // conditions.
    $paths = ['$translatableTexts'];

    if ('Markup' === $element['type']) {
      $paths[] = 'content';
      $elements = NULL;
    }
    else {
      $paths[] = 'label';
      $paths[] = 'description';

      if ('Control' === $element['type']) {
        $paths = array_merge($paths, [
          ['options', 'confirm'],
          ['options', 'placeholder'],
          ['options', 'addButtonLabel'],
          ['options', 'removeButtonLabel'],
        ]);
        $elements = $element['options']['elements'] ?? NULL;
      }
      else {
        $elements = $element['elements'];
      }
    }

    StringExtractUtil::addStrings($strings, $element, $paths);

    if (is_array($elements)) {
      foreach ($elements as $subElement) {
        if ($subElement instanceof JsonSchema) {
          $this->doExtractStrings($strings, $subElement);
        }
      }
    }
  }

}
