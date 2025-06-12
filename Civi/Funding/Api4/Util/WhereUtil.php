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
 * @phpstan-type whereT array<array{string, string|mixed[], 2?: mixed}>
 *
 * @codeCoverageIgnore
 */
final class WhereUtil {

  /**
   * @phpstan-param whereT $where
   */
  public static function containsField(array $where, string ...$field): bool {
    foreach ($where as $clause) {
      if (is_array($clause[1])) {
        // Composite condition.
        // @phpstan-ignore argument.type
        if (self::containsField($clause[1], ...$field)) {
          return TRUE;
        }
      }

      if (in_array($clause[0], $field, TRUE)) {
        return TRUE;
      }
    }

    return FALSE;
  }

  /**
   * @phpstan-param whereT $where
   */
  public static function containsFieldPrefix(array $where, string $fieldPrefix): bool {
    foreach ($where as $clause) {
      if (is_array($clause[1])) {
        // Composite condition.
        // @phpstan-ignore argument.type
        if (self::containsFieldPrefix($clause[1], $fieldPrefix)) {
          return TRUE;
        }
      }

      if (str_starts_with($clause[0], $fieldPrefix)) {
        return TRUE;
      }
    }

    return FALSE;
  }

  /**
   * @phpstan-param whereT $where
   */
  public static function getBool(array $where, string $field): ?bool {
    foreach ($where as $clause) {
      if (is_array($clause[1])) {
        // Composite condition.
        // @phpstan-ignore argument.type
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
   * @phpstan-param whereT $where
   */
  public static function getInt(array $where, string $field): ?int {
    foreach ($where as $clause) {
      if (is_array($clause[1])) {
        // Composite condition.
        // @phpstan-ignore argument.type
        return 'AND' === $clause[0] ? self::getInt($clause[1], $field) : NULL;
      }

      if ($clause[0] === $field && '=' === $clause[1] && is_numeric($clause[2] ?? NULL)) {
        return (int) $clause[2];
      }
    }

    return NULL;
  }

  /**
   * @phpstan-param whereT $where
   * @phpstan-param array<string, string> $fieldReplacements
   *   Field names that match a key in this map will be replaced by the value.
   * @phpstan-param array<int|string, scalar> $valueReplacements
   *   If a field name is replaced, the value is replaced, too, but only if the
   *   value equals a key in this map.
   *
   * @phpstan-return whereT
   */
  public static function replaceField(array $where, array $fieldReplacements, array $valueReplacements = []): array {
    foreach ($where as &$clause) {
      if (is_array($clause[1])) {
        // Composite condition.
        // @phpstan-ignore argument.type
        $clause[1] = self::replaceField($clause[1], $fieldReplacements, $valueReplacements);
      }

      if (isset($fieldReplacements[$clause[0]])) {
        $clause[0] = $fieldReplacements[$clause[0]];
        if (is_scalar($clause[2] ?? NULL) && array_key_exists((string) $clause[2], $valueReplacements)) {
          $clause[2] = $valueReplacements[$clause[2]];
        }
        elseif (is_array($clause[2] ?? NULL)) {
          $clause[2] = array_map(
            fn ($value) => is_scalar($value) && array_key_exists((string) $value, $valueReplacements)
              ? $valueReplacements[$value] : $value,
            $clause[2]
          );
        }
      }
    }

    return $where;
  }

}
