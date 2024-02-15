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

final class ArrayUtil {

  /**
   * Set the given value at the given path. Existing values are replaced. New
   * arrays are created if necessary.
   *
   * @phpstan-param array<int|string, mixed> $array
   * @phpstan-param array<int|string> $path
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

}
