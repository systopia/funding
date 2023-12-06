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
 * @phpstan-type clearingProcessT array{
 *   id?: int,
 *   funding_case_id: int,
 *   status: string,
 *   creation_date: string,
 *   modification_date: string,
 *   report_data: array<string, mixed>,
 * }
 *
 * @phpstan-extends AbstractEntity<clearingProcessT>
 *
 * @codeCoverageIgnore
 */
final class ClearingProcessEntity extends AbstractEntity {

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
   * @phpstan-return array<string, mixed>
   */
  public function getReportData(): array {
    return $this->values['report_data'];
  }

  /**
   * @param array<string, mixed> $reportData
   */
  public function setReportData(array $reportData): self {
    $this->values['report_data'] = $reportData;

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
