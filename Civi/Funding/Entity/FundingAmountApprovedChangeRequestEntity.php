<?php
/*
 * Copyright (C) 2026 SYSTOPIA GmbH
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
 * @phpstan-type fundingAmountApprovedChangeRequestT array{
 *   id?: int,
 *   funding_case_id: int,
 *   status: string,
 *   creation_date: string,
 *   creation_contact_id: int,
 *   amount_requested: float,
 *   comment: string|null,
 *   decision_date: string|null,
 *   decision_contact_id: int|null,
 *   amount_approved: float|null,
 * }
 *
 * @phpstan-extends AbstractEntity<fundingAmountApprovedChangeRequestT>
 */
final class FundingAmountApprovedChangeRequestEntity extends AbstractEntity {

  public function getFundingCaseId(): int {
    return $this->values['funding_case_id'];
  }

  public function setFundingCaseId(int $fundingCaseId): self {
    $this->values['funding_case_id'] = $fundingCaseId;

    return $this;
  }

  public function getStatus(): string {
    return $this->values['status'];
  }

  public function setStatus(string $status): self {
    $this->values['status'] = $status;

    return $this;
  }

  public function getCreationDate(): \DateTime {
    return new \DateTime($this->values['creation_date']);
  }

  public function setCreationDate(\DateTimeInterface $creationDate): self {
    $this->values['creation_date'] = static::toDateTimeStr($creationDate);

    return $this;
  }

  public function getCreationContactId(): int {
    return $this->values['creation_contact_id'];
  }

  public function setCreationContactId(int $creationContactId): self {
    $this->values['creation_contact_id'] = $creationContactId;

    return $this;
  }

  public function getAmountRequested(): float {
    return (float) $this->values['amount_requested'];
  }

  public function setAmountRequested(float $amountRequested): self {
    $this->values['amount_requested'] = $amountRequested;

    return $this;
  }

  public function getComment(): ?string {
    return $this->values['comment'] ?? NULL;
  }

  public function setComment(?string $comment): self {
    $this->values['comment'] = $comment;

    return $this;
  }

  public function getDecisionDate(): ?\DateTime {
    return isset($this->values['decision_date']) ? new \DateTime($this->values['decision_date']) : NULL;
  }

  public function setDecisionDate(?\DateTimeInterface $decisionDate): self {
    $this->values['decision_date'] = ($decisionDate !== NULL) ? static::toDateTimeStr($decisionDate) : NULL;

    return $this;
  }

  public function getDecisionContactId(): ?int {
    return $this->values['decision_contact_id'] ?? NULL;
  }

  public function setDecisionContactId(?int $decisionContactId): self {
    $this->values['decision_contact_id'] = $decisionContactId;

    return $this;
  }

  public function getAmountApproved(): ?float {
    return isset($this->values['amount_approved']) ? (float) $this->values['amount_approved'] : NULL;
  }

  public function setAmountApproved(?float $amountApproved): self {
    $this->values['amount_approved'] = $amountApproved;

    return $this;
  }

}
