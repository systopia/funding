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
 * @phpstan-type applicationResourcesItemT array{
 *   id?: int,
 *   application_process_id: int,
 *   identifier: string,
 *   type: string,
 *   amount: float,
 *   properties: array<int|string, mixed>,
 * }
 *
 * @phpstan-method applicationResourcesItemT toArray()
 * @phpstan-method void setValues(applicationResourcesItemT $values)
 */
final class ApplicationResourcesItemEntity extends AbstractEntity {

  /**
   * @var array
   * @phpstan-var applicationResourcesItemT
   */
  protected array $values;

  /**
   * @phpstan-param applicationResourcesItemT $values
   */
  public static function fromArray(array $values): self {
    return new self($values);
  }

  /**
   * @phpstan-param applicationResourcesItemT $values
   */
  public function __construct(array $values) {
    parent::__construct($values);
  }

  public function getApplicationProcessId(): int {
    return $this->values['application_process_id'];
  }

  public function getIdentifier(): string {
    return $this->values['identifier'];
  }

  public function getType(): string {
    return $this->values['type'];
  }

  public function setType(string $type): self {
    $this->values['type'] = $type;

    return $this;
  }

  public function getAmount(): float {
    return $this->values['amount'];
  }

  public function setAmount(float $amount): self {
    $this->values['amount'] = $amount;

    return $this;
  }

  /**
   * @phpstan-return array<int|string, mixed> JSON serializable array.
   */
  public function getProperties(): array {
    return $this->values['properties'];
  }

  /**
   * @phpstan-param array<int|string, mixed> $properties
   *   JSON serializable array.
   */
  public function setProperties(array $properties): self {
    $this->values['properties'] = $properties;

    return $this;
  }

}
