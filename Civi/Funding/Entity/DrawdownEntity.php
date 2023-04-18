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

namespace Civi\Funding\Entity;

/**
 * @phpstan-type drawdownT array{
 *   id?: int,
 *   payout_process_id: int,
 *   status: string,
 *   creation_date: string,
 *   amount: float,
 *   acception_date: ?string,
 *   requester_contact_id: int,
 *   reviewer_contact_id: ?int,
 * }
 *
 * @phpstan-extends AbstractEntity<drawdownT>
 *
 * @codeCoverageIgnore
 */
final class DrawdownEntity extends AbstractEntity {

  public function getPayoutProcessId(): int {
    return $this->values['payout_process_id'];
  }

  public function getStatus(): string {
    return $this->values['status'];
  }

  public function setStatus(string $status): self {
    $this->values['status'] = $status;

    return $this;
  }

  public function getCreationDate(): \DateTimeInterface {
    return new \DateTime($this->values['creation_date']);
  }

  public function getAmount(): float {
    return $this->values['amount'];
  }

  public function getAcceptionDate(): ?\DateTimeInterface {
    return static::toDateTimeOrNull($this->values['acception_date']);
  }

  public function setAcceptionDate(?\DateTimeInterface $acceptionDate): self {
    $this->values['acception_date'] = static::toDateTimeStrOrNull($acceptionDate);

    return $this;
  }

  public function getRequesterContactId(): int {
    return $this->values['requester_contact_id'];
  }

  public function getReviewerContactId(): ?int {
    return $this->values['reviewer_contact_id'];
  }

  public function setReviewerContactId(?int $reviewerContactId): self {
    $this->values['reviewer_contact_id'] = $reviewerContactId;

    return $this;
  }

  /**
   * On create CiviCRM returns a different date format than on get. This method
   * reformats the dates in $values so that they are as on get.
   *
   * @internal
   */
  public function reformatDates(): self {
    $this->values['creation_date'] = static::toDateTimeStr($this->getCreationDate());
    $this->setAcceptionDate($this->getAcceptionDate());

    return $this;
  }

}
