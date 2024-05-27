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
 * @phpstan-type clearingProcessT array{
 *   id?: int,
 *   application_process_id: int,
 *   status: string,
 *   creation_date: string,
 *   modification_date: string,
 *   report_data: array<int|string, mixed>,
 *   is_review_content: bool|null,
 *   reviewer_cont_contact_id: int|null,
 *   is_review_calculative: bool|null,
 *   reviewer_calc_contact_id: int|null,
 * }
 *
 * @phpstan-extends AbstractEntity<clearingProcessT>
 *
 * @codeCoverageIgnore
 */
final class ClearingProcessEntity extends AbstractEntity {

  public function getApplicationProcessId(): int {
    return $this->values['application_process_id'];
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

  public function getModificationDate(): \DateTimeInterface {
    return new \DateTime($this->values['modification_date']);
  }

  public function setModificationDate(\DateTimeInterface $modificationDate): self {
    $this->values['modification_date'] = static::toDateTimeStr($modificationDate);

    return $this;
  }

  /**
   * @phpstan-return array<int|string, mixed>
   */
  public function getReportData(): array {
    return $this->values['report_data'];
  }

  /**
   * @param array<int|string, mixed> $reportData
   */
  public function setReportData(array $reportData): self {
    $this->values['report_data'] = $reportData;

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

  public function getFullStatus(): FullClearingProcessStatus {
    return new FullClearingProcessStatus(
      $this->getStatus(),
      $this->getIsReviewCalculative(),
      $this->getIsReviewContent()
    );
  }

  public function setFullStatus(FullClearingProcessStatus $fullStatus): self {
    $this->setStatus($fullStatus->getStatus());
    $this->setIsReviewCalculative($fullStatus->getIsReviewCalculative());
    $this->setIsReviewContent($fullStatus->getIsReviewContent());

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

    return $this;
  }

}
