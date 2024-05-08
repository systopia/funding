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

namespace Civi\Funding\Api4\Util;

/**
 * @codeCoverageIgnore
 */
final class WhereUtil {

  /**
   * @phpstan-param array<array{string, string|mixed[], 2?: mixed}> $where
   */
  public static function getBool(array $where, string $field): ?bool {
    foreach ($where as $clause) {
      if (is_array($clause[1])) {
        // Composite condition.
        // @phpstan-ignore-next-line
        return 'AND' === $clause[0] ? self::getBool($clause[1], $field) : NULL;
      }

      if ($clause[0] === $field && '=' === $clause[1] && is_scalar($clause[2] ?? NULL)) {
        return (bool) $clause[2];
      }

      if ($clause[0] === $field && '!=' === $clause[1] && is_scalar($clause[2] ?? NULL)) {
        return !(bool) $clause[2];
      }
    }

    return NULL;
  }

  /**
   * @phpstan-param array<array{string, string|mixed[], 2?: mixed}> $where
   */
  public static function getInt(array $where, string $field): ?int {
    foreach ($where as $clause) {
      if (is_array($clause[1])) {
        // Composite condition.
        // @phpstan-ignore-next-line
        return 'AND' === $clause[0] ? self::getInt($clause[1], $field) : NULL;
      }

      if ($clause[0] === $field && '=' === $clause[1] && is_numeric($clause[2] ?? NULL)) {
        return (int) $clause[2];
      }
    }

    return NULL;
  }

}
