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
 *   receipt_number: ?string,
 *   receipt_date: ?string,
 *   payment_date: string,
 *   recipient: string,
 *   reason: string,
 *   amount: float,
 *   amount_admitted: ?float,
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

  public function getReceiptNumber(): ?string {
    // @phpstan-ignore-next-line
    return $this->values['receipt_number'];
  }

  /**
   * @return static
   */
  public function setReceiptNumber(?string $receiptNumber): self {
    $this->values['receipt_number'] = $receiptNumber;

    return $this;
  }

  public function getReceiptDate(): ?\DateTimeInterface {
    // @phpstan-ignore-next-line
    return self::toDateTimeOrNull($this->values['receipt_date']);
  }

  /**
   * @return static
   */
  public function setReceiptDate(?\DateTimeInterface $receiptDate): self {
    $this->values['receipt_date'] = self::toDateStrOrNull($receiptDate);

    return $this;
  }

  public function getPaymentDate(): \DateTimeInterface {
    // @phpstan-ignore-next-line
    return new \DateTime($this->values['payment_date']);
  }

  /**
   * @return static
   */
  public function setPaymentDate(\DateTimeInterface $paymentDate): self {
    $this->values['payment_date'] = self::toDateStr($paymentDate);

    return $this;
  }

  public function getRecipient(): string {
    // @phpstan-ignore-next-line
    return $this->values['recipient'];
  }

  /**
   * @return static
   */
  public function setRecipient(string $recipient): self {
    $this->values['recipient'] = $recipient;

    return $this;
  }

  public function getReason(): string {
    // @phpstan-ignore-next-line
    return $this->values['reason'];
  }

  /**
   * @return static
   */
  public function setReason(string $reason): self {
    $this->values['reason'] = $reason;

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
  public function setAmountAdmitted(?float $amountAdmitted): self {
    $this->values['amount_admitted'] = $amountAdmitted;

    return $this;
  }

  abstract public function getFinancePlanItemId(): int;

  /**
   * On create CiviCRM returns a different date format than on get. This method
   * reformats the dates in $values so that they are as on get.
   *
   * @return static
   *
   * @internal
   */
  public function reformatDates(): self {
    $this->setPaymentDate($this->getPaymentDate());

    return $this;
  }

}
