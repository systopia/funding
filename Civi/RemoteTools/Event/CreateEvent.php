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

namespace Civi\RemoteTools\Event;

/**
 * @codeCoverageIgnore
 */
class CreateEvent extends AbstractRequestEvent {

  /**
   * @phpstan-var array<string, mixed>
   */
  protected array $values;

  /**
   * @phpstan-var array<array<string, mixed>>
   */
  private array $records = [];

  private ?int $rowCount = NULL;

  /**
   * @param mixed $value
   *
   * @return $this
   */
  public function addValue(string $field, $value): self {
    $this->values[$field] = $value;

    return $this;
  }

  /**
   * @phpstan-return array<string, mixed>
   */
  public function getValues(): array {
    return $this->values;
  }

  /**
   * @phpstan-param array<string, mixed> $values
   *
   * @return $this
   */
  public function setValues(array $values): self {
    $this->values = $values;

    return $this;
  }

  /**
   * @phpstan-return array<array<string, mixed>>
   */
  public function getRecords(): array {
    return $this->records;
  }

  /**
   * @phpstan-param array<array<string, mixed>> $records
   *
   * @return $this
   */
  public function setRecords(array $records): self {
    $this->records = $records;

    return $this;
  }

  public function setRowCount(int $rowCount): self {
    $this->rowCount = $rowCount;

    return $this;
  }

  public function getRowCount(): int {
    return $this->rowCount ?? count($this->records);
  }

  protected function getRequiredParams(): array {
    return ['values'];
  }

}
