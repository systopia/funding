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
 * @phpstan-import-type applicationCostItemT from ApplicationCostItemEntity
 * @phpstan-import-type applicationResourcesItemT from ApplicationResourcesItemEntity
 *
 * @phpstan-type applicationSnapshotT array{
 *   id?: int,
 *   application_process_id: int,
 *   status: string,
 *   creation_date: string,
 *   title: string,
 *   short_description: string,
 *   start_date: ?string,
 *   end_date: ?string,
 *   request_data: array<string, mixed>,
 *   cost_items: array<applicationCostItemT>,
 *   resources_items: array<applicationResourcesItemT>,
 *   amount_requested: double,
 *   is_review_content: ?bool,
 *   is_review_calculative: ?bool,
 *   is_eligible: ?bool,
 *   is_in_work: bool,
 *   is_rejected: bool,
 *   is_withdrawn: bool,
 *   custom_fields: array<string, mixed>,
 * }
 *
 * @extends AbstractEntity<applicationSnapshotT>
 */
final class ApplicationSnapshotEntity extends AbstractEntity {

  public function getApplicationProcessId(): int {
    return $this->values['application_process_id'];
  }

  public function getStatus(): string {
    return $this->values['status'];
  }

  public function getCreationDate(): \DateTimeInterface {
    return new \DateTime($this->values['creation_date']);
  }

  public function getTitle(): string {
    return $this->values['title'];
  }

  public function getShortDescription(): string {
    return $this->values['short_description'];
  }

  public function getStartDate(): ?\DateTime {
    return static::toDateTimeOrNull($this->values['start_date']);
  }

  public function getEndDate(): ?\DateTime {
    return static::toDateTimeOrNull($this->values['end_date']);
  }

  /**
   * @phpstan-return array<string, mixed>
   */
  public function getRequestData(): array {
    return $this->values['request_data'];
  }

  /**
   * @phpstan-return array<applicationCostItemT>
   */
  public function getCostItems(): array {
    return $this->values['cost_items'];
  }

  /**
   * @phpstan-return array<applicationResourcesItemT>
   */
  public function getResourcesItems(): array {
    return $this->values['resources_items'];
  }

  public function getAmountRequested(): float {
    return $this->values['amount_requested'];
  }

  public function getIsReviewContent(): ?bool {
    return $this->values['is_review_content'];
  }

  public function getIsReviewCalculative(): ?bool {
    return $this->values['is_review_calculative'];
  }

  public function getIsEligible(): ?bool {
    return $this->values['is_eligible'];
  }

  public function setIsEligible(?bool $isEligibleStatus): self {
    $this->values['is_eligible'] = $isEligibleStatus;

    return $this;
  }

  public function getIsInWork(): bool {
    return $this->values['is_in_work'];
  }

  public function setIsInWork(bool $isInWork): self {
    $this->values['is_in_work'] = $isInWork;

    return $this;
  }

  public function getIsRejected(): bool {
    return $this->values['is_rejected'];
  }

  public function setIsRejected(bool $isRejected): self {
    $this->values['is_rejected'] = $isRejected;

    return $this;
  }

  public function getIsWithdrawn(): bool {
    return $this->values['is_withdrawn'];
  }

  public function setIsWithdrawn(bool $isWithdrawn): self {
    $this->values['is_withdrawn'] = $isWithdrawn;

    return $this;
  }

  /**
   * @phpstan-return array<string, mixed>
   */
  public function getCustomFields(): array {
    return $this->values['custom_fields'];
  }

  /**
   * @phpstan-param array<string, mixed> $customFields
   */
  public function setCustomFields(array $customFields): self {
    $this->values['custom_fields'] = $customFields;

    return $this;
  }

}
