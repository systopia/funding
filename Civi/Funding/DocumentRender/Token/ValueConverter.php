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

use Brick\Money\Money;

final class ValueConverter {

  /**
   * @param mixed $value
   */
  public static function toResolvedToken($value): ResolvedToken {
    if (self::isValueConvertedByTokenProcessor($value)) {
      return new ResolvedToken($value, 'text/plain');
    }

    $string = self::valueToString($value);
    if (\is_array($value) || is_object($value) && \str_contains($string, "\n")) {
      return new ResolvedToken(
        \nl2br(\htmlentities($string)),
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
      $string = implode("\n", $items);

      // Ensure there's one, but only one "\n" at the end.
      return str_ends_with($string, "\n") ? $string : $string . "\n";
    }

    if (\is_object($value)) {
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

  /**
   * @param mixed $value
   *
   * @return bool TRUE, if $value is converted to string by TokenProcessor.
   *
   * @phpstan-assert-if-true \DateTime|Money $value
   *
   * @see \Civi\Token\TokenProcessor::filterTokenValue()
   */
  private static function isValueConvertedByTokenProcessor($value): bool {
    return $value instanceof \DateTime || $value instanceof Money;
  }

}
