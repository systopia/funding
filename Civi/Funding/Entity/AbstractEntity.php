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

/**
 * Wrapper class for the entity arrays returned by CiviCRM API. This class
 * requires that the entity has a primary key named "id" of type unsigned int.
 * "check_permissions" and "custom" are automatically added by CiviCRM since
 * version 5.53.
 *
 * @phpstan-type entityT array<string, mixed>&array{
 *   id?: int,
 *   check_permissions?: bool,
 *   custom?: mixed,
 * }
 */
abstract class AbstractEntity {

  /**
   * @var array
   * @phpstan-var entityT
   */
  protected array $values;

  /**
   * @phpstan-param entityT $values
   */
  protected function __construct(array $values) {
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
    return $this->values['id'] ?? -1;
  }

  public function isNew(): bool {
    return -1 === $this->getId();
  }

  /**
   * @phpstan-param entityT $values
   *
   * @internal
   */
  public function setValues(array $values): void {
    $this->values = $values;
  }

  /**
   * @phpstan-return entityT
   */
  public function toArray(): array {
    return $this->values;
  }

  protected static function toDateTimeOrNull(?string $dateTimeStr): ?\DateTime {
    return NULL === $dateTimeStr ? NULL : new \DateTime($dateTimeStr);
  }

  protected static function toDateTimeStr(\DateTimeInterface $dateTime): string {
    return $dateTime->format('Y-m-d H:i:s');
  }

  protected static function toDateTimeStrOrNull(?\DateTimeInterface $dateTime): ?string {
    return NULL === $dateTime ? NULL : $dateTime->format('Y-m-d H:i:s');
  }

}
