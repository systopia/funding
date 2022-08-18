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
 *   funding_case_id: int,
 *   status: string,
 *   creation_date: string,
 *   modification_date: string,
 *   title: string,
 *   short_description: string,
 *   start_date: string|null,
 *   end_date: string|null,
 *   request_data: array<int|string, mixed>,
 *   amount_granted: float|null,
 *   granted_budget: float|null,
 *   is_review_content: bool|null,
 *   is_review_calculative: bool|null,
 * }
 *
 * @phpstan-method applicationProcessT toArray()
 */
final class ApplicationProcessEntity extends AbstractEntity {

  /**
   * @var array
   * @phpstan-var applicationProcessT
   */
  protected array $values;

  /**
   * @phpstan-param applicationProcessT $values
   */
  public static function fromArray(array $values): self {
    return new self($values);
  }

  /**
   * @phpstan-param applicationProcessT $values
   */
  public function __construct(array $values) {
    parent::__construct($values);
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

  /**
   * @return string  date in the form "YmdHis".
   */
  public function getCreationDate(): string {
    return $this->values['creation_date'];
  }

  /**
   * @return string Modification date in the form "YmdHis".
   */
  public function getModificationDate(): string {
    return $this->values['modification_date'];
  }

  /**
   * @param string $modificationDate Modification date in the form "YmdHis".
   */
  public function setModificationDate(string $modificationDate): self {
    $this->values['modification_date'] = $modificationDate;

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

  /**
   * @return string|null Start date in the form "YmdHis".
   */
  public function getStartDate(): ?string {
    return $this->values['start_date'];
  }

  /**
   * @param string|null $startDate Start date in the form "YmdHis".
   */
  public function setStartDate(?string $startDate): self {
    $this->values['start_date'] = $startDate;

    return $this;
  }

  /**
   * @return string|null End date in the form "YmdHis".
   */
  public function getEndDate(): ?string {
    return $this->values['end_date'];
  }

  /**
   * @param string|null $endDate End date in the form "YmdHis".
   */
  public function setEndDate(?string $endDate): self {
    $this->values['end_date'] = $endDate;

    return $this;
  }

  /**
   * @phpstan-return array<int|string, mixed>
   */
  public function getRequestData(): array {
    return $this->values['request_data'];
  }

  /**
   * @phpstan-param array<int|string, mixed> $requestData
   */
  public function setRequestData(array $requestData): self {
    $this->values['request_data'] = $requestData;

    return $this;
  }

  public function getAmountGranted(): ?float {
    return $this->values['amount_granted'];
  }

  public function setAmountGranted(?float $amountGranted): self {
    $this->values['amount_granted'] = $amountGranted;

    return $this;
  }

  public function getGrantedBudget(): ?float {
    return $this->values['granted_budget'];
  }

  public function setGrantedBudget(?float $grantedBudget): self {
    $this->values['granted_budget'] = $grantedBudget;

    return $this;
  }

  public function getIsReviewContent(): ?bool {
    return $this->values['is_review_content'];
  }

  public function setIsReviewContent(?bool $isReviewContent): self {
    $this->values['is_review_content'] = $isReviewContent;

    return $this;
  }

  public function getIsReviewCalculative(): ?bool {
    return $this->values['is_review_calculative'];
  }

  public function setIsReviewCalculative(?bool $isReviewCalculative): self {
    $this->values['is_review_calculative'] = $isReviewCalculative;

    return $this;
  }

}
