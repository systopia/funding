<?php
/*
 * Copyright (C) 2022 SYSTOPIA GmbH
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

namespace Civi\RemoteTools\Form\JsonSchema;

class JsonSchema implements \JsonSerializable {

  /**
   * @var array<string, scalar|self|null|array<int, scalar|self|null>>
   */
  protected array $keywords;

  /**
   * @param array<int, mixed> $array
   *
   * @return array<int, scalar|self|null>
   */
  public static function convertToJsonSchemaArray(array $array): array {
    return \array_values(\array_map(function ($value) {
      if (\is_array($value)) {
        if (!\is_string(key($value))) {
          throw new \InvalidArgumentException('Expected associative array got non-associative array');
        }

        return self::fromArray($value);
      }

      static::assertAllowedValue($value);

      /** @var scalar|self|null $value */
      return $value;
    }, $array));
  }

  /**
   * @param array<string, mixed> $array Array containing scalars, NULL, or
   *   JsonSchema objects, and arrays containing values of these three types.
   *
   * @return self
   */
  public static function fromArray(array $array): self {
    foreach ($array as $key => $value) {
      if (\is_array($value)) {
        if (\is_string(key($value))) {
          $array[$key] = self::fromArray($value);
        }
        else {
          $array[$key] = self::convertToJsonSchemaArray($value);
        }
      }
      else {
        static::assertAllowedValue($value);
      }
    }

    /** @var array<string, scalar|self|null|array<int, scalar|self|null>> $array */
    return new self($array);
  }

  /**
   * @param mixed $value
   *
   * @return void
   */
  protected static function assertAllowedValue($value): void {
    if (!static::isAllowedValue($value)) {
      throw new \InvalidArgumentException(
        \sprintf(
          'Expected scalar, %s, NULL, or non-associative array containing those three types, got "%s"',
          self::class,
          \is_object($value) ? \get_class($value) : \gettype($value),
        )
      );
    }
  }

  /**
   * @param mixed $value
   *
   * @return bool
   *   True if value is scalar|self|null|array<int, scalar|self|null>.
   */
  protected static function isAllowedValue($value): bool {
    if (!\is_array($value)) {
      $value = [$value];
    }

    foreach ($value as $k => $v) {
      if (!\is_int($k) || (!\is_scalar($v) && !$v instanceof self && NULL !== $v)) {
        return FALSE;
      }
    }

    return TRUE;
  }

  /**
   * @param array<string, scalar|self|null|array<int, scalar|self|null>> $keywords
   */
  public function __construct(array $keywords) {
    $this->keywords = $keywords;
  }

  /**
   * @param string $keyword
   * @param scalar|self|null|array<int, scalar|self|null> $value
   *
   * @return $this
   */
  public function addKeyword(string $keyword, $value): self {
    if ($this->hasKeyword($keyword)) {
      throw new \InvalidArgumentException(\sprintf('Keyword "%s" already exists', $keyword));
    }

    $this->keywords[$keyword] = $value;

    return $this;
  }

  /**
   * @return array<string, scalar|self|null|array<int, scalar|self|null>>
   */
  public function getKeywords(): array {
    return $this->keywords;
  }

  public function hasKeyword(string $keyword): bool {
    return isset($this->keywords[$keyword]);
  }

  /**
   * @param string $keyword
   *
   * @return scalar|self|null|array<int, scalar|self|null>
   */
  public function getKeywordValue(string $keyword) {
    if (!$this->hasKeyword($keyword)) {
      throw new \InvalidArgumentException(\sprintf('No such keyword "%s"', $keyword));
    }

    return $this->keywords[$keyword];
  }

  /**
   * @return array<string, mixed> Values are of type array|scalar|null with leaves of type array{}|scalar|null.
   */
  public function toArray(): array {
    return \array_map(function ($value) {
      if ($value instanceof self) {
        return $value->toArray();
      }
      elseif (\is_array($value)) {
        return \array_values(\array_map(fn ($value) => $value instanceof self ? $value->toArray() : $value, $value));
      }

      return $value;
    }, $this->keywords);
  }

  /**
   * @return \stdClass
   *   Properties are of type \stdClass|array|scalar|null with leaf properties
   *   of type array{}|scalar|null.
   */
  public function toStdClass(): \stdClass {
    return (object) \array_map(function ($value) {
      if ($value instanceof self) {
        return $value->toStdClass();
      }
      elseif (\is_array($value)) {
        return \array_values(\array_map(fn ($value) => $value instanceof self ? $value->toStdClass() : $value, $value));
      }

      return $value;
    }, $this->keywords);
  }

  /**
   * @inheritDoc
   */
  #[\ReturnTypeWillChange]
  public function jsonSerialize() {
    return $this->toArray();
  }

}
