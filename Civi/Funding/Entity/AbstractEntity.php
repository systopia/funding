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

namespace Civi\Funding\Entity;

use Civi\Api4\Generic\Result;
use Civi\Funding\Util\DateTimeUtil;

/**
 * Wrapper class for the entity arrays returned by CiviCRM API. This class
 * requires that the entity has a primary key named "id" of type unsigned int.
 * "check_permissions" and "custom" are automatically added by CiviCRM since
 * version 5.53.
 *
 * @template T of array<string, mixed>
 *
 * T should contain `id?: int`.
 *
 * @phpstan-consistent-constructor
 */
abstract class AbstractEntity {

  /**
   * @phpstan-var T
   */
  protected array $values;

  /**
   * @phpstan-return array<int|string, static>
   *   The keys of the given result are preserved.
   */
  public static function allFromApiResult(Result $result): array {
    // @phpstan-ignore argument.type, argument.type
    return array_map(fn (array $record) => static::fromArray($record), $result->getArrayCopy());
  }

  /**
   * @return static
   *
   * @throws \CRM_Core_Exception
   */
  public static function singleFromApiResult(Result $result): self {
    // @phpstan-ignore argument.type
    return static::fromArray($result->single());
  }

  /**
   * @return static|null
   *
   * @throws \CRM_Core_Exception
   */
  public static function singleOrNullFromApiResult(Result $result): ?self {
    if ($result->count() > 1) {
      throw new \CRM_Core_Exception(sprintf('Expected zero or one record, got %d records.', $result->count()));
    }

    $values = $result->first();

    // @phpstan-ignore argument.type
    return NULL === $values ? NULL : static::fromArray($values);
  }

  /**
   * @phpstan-param T $values
   *
   * @return static
   */
  public static function fromArray(array $values): self {
    // @phpstan-ignore return.type
    return new static($values);
  }

  /**
   * @phpstan-param T|null $values
   *
   * @return static|null
   */
  public static function fromArrayOrNull(?array $values): ?self {
    return NULL === $values ? NULL : static::fromArray($values);
  }

  /**
   * @phpstan-param T $values
   */
  public function __construct(array $values) {
    // Unset extra values returned on create action since CiviCRM 5.53.
    unset($values['custom']);
    unset($values['check_permissions']);

    // @phpstan-ignore assign.propertyType
    $this->values = $values;
  }

  /**
   * @param string $key
   * @param mixed $default
   *
   * @return mixed
   */
  public function get(string $key, $default = NULL) {
    return $this->values[$key] ?? $default;
  }

  /**
   * @return int Returns -1 for a new, unpersisted entity.
   */
  public function getId(): int {
    /** @phpstan-ignore-next-line  */
    return $this->values['id'] ?? -1;
  }

  public function has(string $key): bool {
    return array_key_exists($key, $this->values);
  }

  public function isNew(): bool {
    return -1 === $this->getId();
  }

  /**
   * @phpstan-param T $values
   *
   * @internal
   */
  public function setValues(array $values): void {
    $this->values = $values;
  }

  /**
   * @phpstan-return T
   */
  public function toArray(): array {
    return $this->values;
  }

  protected static function toDateStr(\DateTimeInterface $dateTime): string {
    return DateTimeUtil::toDateStr($dateTime);
  }

  protected static function toDateStrOrNull(?\DateTimeInterface $dateTime): ?string {
    return DateTimeUtil::toDateStrOrNull($dateTime);
  }

  protected static function toDateTimeOrNull(?string $dateTimeStr): ?\DateTime {
    return DateTimeUtil::toDateTimeOrNull($dateTimeStr);
  }

  protected static function toDateTimeStr(\DateTimeInterface $dateTime): string {
    return DateTimeUtil::toDateTimeStr($dateTime);
  }

  protected static function toDateTimeStrOrNull(?\DateTimeInterface $dateTime): ?string {
    return DateTimeUtil::toDateTimeStrOrNull($dateTime);
  }

}
