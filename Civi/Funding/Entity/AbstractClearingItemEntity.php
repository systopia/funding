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
 *   payment_date: ?string,
 *   payment_party: ?string,
 *   reason: ?string,
 *   amount: float,
 *   amount_admitted: ?float,
 *   properties: ?array<string, mixed>,
 *   form_key: string,
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
    // @phpstan-ignore assign.propertyType
    $this->values['status'] = $status;

    return $this;
  }

  public function getFileId(): ?int {
    // @phpstan-ignore return.type
    return $this->values['file_id'];
  }

  /**
   * @return static
   */
  public function setFileId(?int $fileId): self {
    // @phpstan-ignore assign.propertyType
    $this->values['file_id'] = $fileId;

    return $this;
  }

  public function getReceiptNumber(): ?string {
    // @phpstan-ignore return.type
    return $this->values['receipt_number'];
  }

  /**
   * @return static
   */
  public function setReceiptNumber(?string $receiptNumber): self {
    // @phpstan-ignore assign.propertyType
    $this->values['receipt_number'] = $receiptNumber;

    return $this;
  }

  public function getReceiptDate(): ?\DateTimeInterface {
    // @phpstan-ignore argument.type
    return self::toDateTimeOrNull($this->values['receipt_date']);
  }

  /**
   * @return static
   */
  public function setReceiptDate(?\DateTimeInterface $receiptDate): self {
    // @phpstan-ignore assign.propertyType
    $this->values['receipt_date'] = self::toDateStrOrNull($receiptDate);

    return $this;
  }

  public function getPaymentDate(): ?\DateTimeInterface {
    // @phpstan-ignore argument.type
    return self::toDateTimeOrNull($this->values['payment_date']);
  }

  /**
   * @return static
   */
  public function setPaymentDate(?\DateTimeInterface $paymentDate): self {
    // @phpstan-ignore assign.propertyType
    $this->values['payment_date'] = self::toDateStrOrNull($paymentDate);

    return $this;
  }

  public function getPaymentParty(): ?string {
    // @phpstan-ignore return.type
    return $this->values['payment_party'];
  }

  /**
   * @return static
   */
  public function setPaymentParty(?string $paymentParty): self {
    // @phpstan-ignore assign.propertyType
    $this->values['payment_party'] = $paymentParty;

    return $this;
  }

  public function getReason(): ?string {
    // @phpstan-ignore return.type
    return $this->values['reason'];
  }

  /**
   * @return static
   */
  public function setReason(?string $reason): self {
    // @phpstan-ignore assign.propertyType
    $this->values['reason'] = $reason;

    return $this;
  }

  public function getAmount(): float {
    // @phpstan-ignore return.type
    return $this->values['amount'];
  }

  /**
   * @return static
   */
  public function setAmount(float $amount): self {
    // @phpstan-ignore assign.propertyType
    $this->values['amount'] = $amount;

    return $this;
  }

  public function getAmountAdmitted(): ?float {
    // @phpstan-ignore return.type
    return $this->values['amount_admitted'];
  }

  /**
   * @return static
   */
  public function setAmountAdmitted(?float $amountAdmitted): self {
    // @phpstan-ignore assign.propertyType
    $this->values['amount_admitted'] = $amountAdmitted;

    return $this;
  }

  /**
   * @phpstan-return array<string, mixed>|null
   */
  public function getProperties(): ?array {
    // @phpstan-ignore return.type
    return $this->values['properties'];
  }

  /**
   * @phpstan-param array<string, mixed>|null $properties
   */
  public function setProperties(?array $properties): static {
    // @phpstan-ignore assign.propertyType
    $this->values['properties'] = $properties;

    return $this;
  }

  public function getFormKey(): string {
    // @phpstan-ignore return.type
    return $this->values['form_key'];
  }

  public function setFormKey(string $formKey): static {
    // @phpstan-ignore assign.propertyType
    $this->values['form_key'] = $formKey;

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
    $this->setReceiptDate($this->getReceiptDate());
    $this->setPaymentDate($this->getPaymentDate());

    return $this;
  }

}
