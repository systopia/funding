<?php
/*
 * Copyright (C) 2023 SYSTOPIA GmbH
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

namespace Civi\Funding\DocumentRender\Token;

use CRM_Funding_ExtensionUtil as E;

final class ValueConverter {

  /**
   * @param mixed $value
   */
  public static function toResolvedToken($value): ResolvedToken {
    $string = self::valueToString($value);
    if (\is_array($value) || is_object($value) && \str_contains($string, "\n")) {
      return new ResolvedToken(
        '<p>' . \str_replace("\n", '<br/>', \htmlentities($string)) . '</p>',
        'text/html',
      );
    }

    return new ResolvedToken($string, 'text/plain');
  }

  /**
   * @param mixed $value
   */
  private static function valueToString($value, int $listIndent = 4): string {
    if (\is_bool($value)) {
      return $value ? '1' : '0';
    }

    if (\is_array($value)) {
      $items = array_map(
        function($item) use ($listIndent) {
          if (\is_array($item)) {
            return self::valueToString($item, $listIndent + 4);
          }

          return str_repeat(' ', $listIndent) . '- ' . self::valueToString($item, $listIndent + 4);
        }, $value
      );
      return implode("\n", $items);
    }

    if (\is_object($value)) {
      if ($value instanceof \DateTimeInterface) {
        return $value->format(E::ts('Y-m-d H:i:s'));
      }

      if (\method_exists($value, '__toString')) {
        $value->__toString();
      }

      return \get_class($value);
    }

    if (\is_resource($value)) {
      return '';
    }

    // @phpstan-ignore-next-line
    return (string) $value;
  }

}
