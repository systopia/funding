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

namespace Civi\Funding\Util;

use Opis\JsonSchema\JsonPointer;

final class ArrayUtil {

  /**
   * Removes the first occurrence of the given value.
   *
   * @param array<mixed> $array
   *
   * @return bool TRUE if the given value was found, FALSE otherwise.
   */
  public static function removeFirstOccurrence(array &$array, mixed $value): bool {
    $index = array_search($value, $array, TRUE);
    if ($index !== FALSE) {
      unset($array[$index]);
    }

    return FALSE !== $index;
  }

  /**
   * Similar to array_merge_recursive(), but only performs recursive merge if
   * both values are arrays. Otherwise, the latter one overwrites the previous
   * one.
   *
   * @phpstan-param array<mixed> $array1
   * @phpstan-param array<mixed> $array2
   * @phpstan-param array<mixed> ...$arrays
   *
   * @phpstan-return array<mixed>
   */
  public static function mergeRecursive(array $array1, array $array2, array ...$arrays): array {
    $arrays = [$array1, $array2, ...$arrays];

    $merged = [];
    while ($arrays) {
      $array = array_shift($arrays);
      foreach ($array as $key => $value) {
        if (is_string($key)) {
          if (is_array($value) && array_key_exists($key, $merged) && is_array($merged[$key])) {
            $merged[$key] = self::mergeRecursive($merged[$key], $value);
          }
          else {
            $merged[$key] = $value;
          }
        }
        else {
          $merged[] = $value;
        }
      }
    }

    return $merged;
  }

  /**
   * Set the given value at the given path. Existing values are replaced. New
   * arrays are created if necessary.
   *
   * @phpstan-param array<int|string, mixed> $array
   * @phpstan-param list<int|string> $path
   *
   * @param mixed $value
   */
  public static function setValue(array &$array, array $path, $value): void {
    $ref = &$array;
    foreach ($path as $pathElement) {
      if (!is_array($ref)) {
        $ref = [$pathElement => NULL];
      }
      elseif (!array_key_exists($pathElement, $ref)) {
        $ref[$pathElement] = NULL;
      }

      $ref = &$ref[$pathElement];
    }

    $ref = $value;
  }

  /**
   * Same as setValue(), but with JSON pointer instead of path.
   *
   * @phpstan-param non-empty-string $pointer
   * @phpstan-param array<int|string, mixed> $array
   *
   * @param mixed $value
   *
   * @see setValue()
   */
  public static function setValueAtPointer(array &$array, string $pointer, $value): void {
    $parsedPointer = JsonPointer::parse($pointer);
    if (NULL === $parsedPointer) {
      throw new \InvalidArgumentException(sprintf('Invalid JSON pointer "%s"', $pointer));
    }

    self::setValue($array, $parsedPointer->path(), $value);
  }

}
