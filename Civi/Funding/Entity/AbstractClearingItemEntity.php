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
 * @phpstan-type clearingItemT array<string, mixed>&array{
 *   id?: int,
 *   clearing_process_id: int,
 *   status: string,
 *   file_id: ?int,
 *   amount: float,
 *   amount_admitted: ?float,
 *   description: string,
 * }
 *
 * @phpstan-template T of array<string, mixed> //+clearingItemT
 *
 * @phpstan-extends AbstractEntity<T>
 */
abstract class AbstractClearingItemEntity extends AbstractEntity {

  public function getClearingProcessId(): int {
    // @phpstan-ignore-next-line
    return $this->values['clearing_process_id'];
  }

  public function getStatus(): string {
    // @phpstan-ignore-next-line
    return $this->values['status'];
  }

  /**
   * @return static
   */
  public function setStatus(string $status): self {
    $this->values['status'] = $status;

    return $this;
  }

  public function getFileId(): ?int {
    // @phpstan-ignore-next-line
    return $this->values['file_id'];
  }

  /**
   * @return static
   */
  public function setFileId(?int $fileId): self {
    $this->values['file_id'] = $fileId;

    return $this;
  }

  public function getAmount(): float {
    // @phpstan-ignore-next-line
    return $this->values['amount'];
  }

  /**
   * @return static
   */
  public function setAmount(float $amount): self {
    $this->values['amount'] = $amount;

    return $this;
  }

  public function getAmountAdmitted(): ?float {
    // @phpstan-ignore-next-line
    return $this->values['amount_admitted'];
  }

  /**
   * @return static
   */
  public function setAmountAdmitted(float $amountAdmitted): self {
    $this->values['amount_admitted'] = $amountAdmitted;

    return $this;
  }

  public function getDescription(): string {
    // @phpstan-ignore-next-line
    return $this->values['description'];
  }

  /**
   * @return static
   */
  public function setDescription(string $description): self {
    $this->values['description'] = $description;

    return $this;
  }

}
