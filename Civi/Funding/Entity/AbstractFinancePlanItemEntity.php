<?php
/*
 * Copyright (C) 2024 SYSTOPIA GmbH
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
 * @phpstan-type financePlanItemT array{
 *   id?: int,
 *   application_process_id: int,
 *   identifier: non-empty-string,
 *   type: non-empty-string,
 *   amount: float,
 *   properties: array<string, mixed>,
 *   data_pointer: string,
 * }
 *
 * data_pointer is empty for items created before financial plan items were
 * specified in the JSON schema.
 *
 * @phpstan-extends AbstractEntity<financePlanItemT>
 */
abstract class AbstractFinancePlanItemEntity extends AbstractEntity {

  public function getApplicationProcessId(): int {
    return $this->values['application_process_id'];
  }

  /**
   * @return non-empty-string
   */
  public function getIdentifier(): string {
    return $this->values['identifier'];
  }

  /**
   * @return non-empty-string
   */
  public function getType(): string {
    return $this->values['type'];
  }

  /**
   * @param non-empty-string $type
   */
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
   * @phpstan-return array<string, mixed> JSON serializable array.
   */
  public function getProperties(): array {
    return $this->values['properties'];
  }

  /**
   * @phpstan-param array<string, mixed> $properties
   *   JSON serializable array.
   */
  public function setProperties(array $properties): self {
    $this->values['properties'] = $properties;

    return $this;
  }

  public function getDataPointer(): string {
    return $this->values['data_pointer'];
  }

  public function setDataPointer(string $dataPointer): self {
    $this->values['data_pointer'] = $dataPointer;

    return $this;
  }

}
