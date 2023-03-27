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
 * @phpstan-type applicationProcessT array{
 *   id?: int,
 *   identifier: string,
 *   funding_case_id: int,
 *   status: string,
 *   creation_date: string,
 *   modification_date: string,
 *   title: string,
 *   short_description: string,
 *   start_date: string|null,
 *   end_date: string|null,
 *   request_data: array<string, mixed>,
 *   amount_requested: float,
 *   is_review_content: bool|null,
 *   reviewer_cont_contact_id: int|null,
 *   is_review_calculative: bool|null,
 *   reviewer_calc_contact_id: int|null,
 *   is_eligible: bool|null,
 *   check_permissions?: bool,
 *   custom?: mixed,
 * }
 *
 * @phpstan-extends AbstractEntity<applicationProcessT>
 */
final class ApplicationProcessEntity extends AbstractEntity {

  private ?ApplicationSnapshotEntity $restoredSnapshot = NULL;

  public function getIdentifier(): string {
    return $this->values['identifier'];
  }

  public function setIdentifier(string $identifier): self {
    $this->values['identifier'] = $identifier;

    return $this;
  }

  public function getFundingCaseId(): int {
    return $this->values['funding_case_id'];
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

  public function getModificationDate(): \DateTime {
    return new \DateTime($this->values['modification_date']);
  }

  public function setModificationDate(\DateTimeInterface $modificationDate): self {
    $this->values['modification_date'] = static::toDateTimeStr($modificationDate);

    return $this;
  }

  public function getTitle(): string {
    return $this->values['title'];
  }

  public function setTitle(string $title): self {
    $this->values['title'] = $title;

    return $this;
  }

  public function getShortDescription(): string {
    return $this->values['short_description'];
  }

  public function setShortDescription(string $shortDescription): self {
    $this->values['short_description'] = $shortDescription;

    return $this;
  }

  public function getStartDate(): ?\DateTime {
    return static::toDateTimeOrNull($this->values['start_date']);
  }

  public function setStartDate(?\DateTimeInterface $startDate): self {
    $this->values['start_date'] = static::toDateTimeStrOrNull($startDate);

    return $this;
  }

  public function getEndDate(): ?\DateTime {
    return static::toDateTimeOrNull($this->values['end_date']);
  }

  public function setEndDate(?\DateTimeInterface $endDate): self {
    $this->values['end_date'] = static::toDateTimeStrOrNull($endDate);

    return $this;
  }

  /**
   * @phpstan-return array<string, mixed>
   */
  public function getRequestData(): array {
    return $this->values['request_data'];
  }

  /**
   * @phpstan-param array<string, mixed> $requestData
   */
  public function setRequestData(array $requestData): self {
    $this->values['request_data'] = $requestData;

    return $this;
  }

  public function getAmountRequested(): float {
    return $this->values['amount_requested'];
  }

  public function setAmountRequested(float $amountRequested): self {
    $this->values['amount_requested'] = $amountRequested;

    return $this;
  }

  public function getIsReviewContent(): ?bool {
    return $this->values['is_review_content'];
  }

  public function setIsReviewContent(?bool $isReviewContent): self {
    $this->values['is_review_content'] = $isReviewContent;

    return $this;
  }

  public function getReviewerContentContactId(): ?int {
    return $this->values['reviewer_cont_contact_id'];
  }

  public function setReviewerContentContactId(?int $reviewerContentContactId): self {
    $this->values['reviewer_cont_contact_id'] = $reviewerContentContactId;

    return $this;
  }

  public function getIsReviewCalculative(): ?bool {
    return $this->values['is_review_calculative'];
  }

  public function setIsReviewCalculative(?bool $isReviewCalculative): self {
    $this->values['is_review_calculative'] = $isReviewCalculative;

    return $this;
  }

  public function getReviewerCalculativeContactId(): ?int {
    return $this->values['reviewer_calc_contact_id'];
  }

  public function setReviewerCalculativeContactId(?int $reviewerCalculativeContactId): self {
    $this->values['reviewer_calc_contact_id'] = $reviewerCalculativeContactId;

    return $this;
  }

  public function getIsEligible(): ?bool {
    return $this->values['is_eligible'];
  }

  public function setIsEligible(?bool $isEligibleStatus): self {
    $this->values['is_eligible'] = $isEligibleStatus;

    return $this;
  }

  public function getFullStatus(): FullApplicationProcessStatus {
    return new FullApplicationProcessStatus(
      $this->getStatus(),
      $this->getIsReviewCalculative(),
      $this->getIsReviewContent()
    );
  }

  public function setFullStatus(FullApplicationProcessStatus $fullStatus): self {
    $this->setStatus($fullStatus->getStatus());
    $this->setIsReviewCalculative($fullStatus->getIsReviewCalculative());
    $this->setIsReviewContent($fullStatus->getIsReviewContent());

    return $this;
  }

  /**
   * @return \Civi\Funding\Entity\ApplicationSnapshotEntity|null
   *   The application snapshot this application process is currently restored
   *   to. If NULL, it is not in the process of restoring.
   */
  public function getRestoredSnapshot(): ?ApplicationSnapshotEntity {
    return $this->restoredSnapshot;
  }

  /**
   * Sets the application snapshot this application process is currently restored
   * to.
   */
  public function setRestoredSnapshot(?ApplicationSnapshotEntity $restoredSnapshot): self {
    $this->restoredSnapshot = $restoredSnapshot;

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
    $this->setModificationDate($this->getModificationDate());
    $this->setStartDate($this->getStartDate());
    $this->setEndDate($this->getEndDate());

    return $this;
  }

}
