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

final class UiSchemaStringTranslator {

  /**
   * @param array<string, string> $translations
   *   Mapping of original string to translation.
   */
  public function translateStrings(JsonSchema $element, array $translations, string $defaultLocale): void {
    if ('Markup' === $element['type']) {
      StringTranslateUtil::translateStrings($element, ['content'], $translations, $defaultLocale);

      return;
    }

    StringTranslateUtil::translateStrings(
      $element,
      ['label', 'description'],
      $translations,
      $defaultLocale
    );
    if ('Control' === $element['type']) {
      StringTranslateUtil::translateStrings($element, [
        ['options', 'confirm'],
        ['options', 'placeholder'],
        ['options', 'addButtonLabel'],
        ['options', 'removeButtonLabel'],
      ], $translations, $defaultLocale);

      $elements = $element['options']['elements'] ?? NULL;
    }
    else {
      $elements = $element['elements'];
    }

    if (is_array($elements)) {
      foreach ($elements as $subElement) {
        if ($subElement instanceof JsonSchema) {
          $this->translateStrings($subElement, $translations, $defaultLocale);
        }
      }
    }
  }

}
