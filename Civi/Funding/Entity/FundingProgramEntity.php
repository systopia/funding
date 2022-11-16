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
 * @phpstan-type fundingProgramT array{
 *   id?: int,
 *   title: string,
 *   abbreviation: string,
 *   start_date: string,
 *   end_date: string,
 *   requests_start_date: string,
 *   requests_end_date: string,
 *   currency: string,
 *   budget: float|null,
 *   permissions?: array<string>,
 * }
 *
 * @phpstan-extends AbstractEntity<fundingProgramT>
 */
final class FundingProgramEntity extends AbstractEntity {

  public function getTitle(): string {
    return $this->values['title'];
  }

  public function setTitle(string $title): self {
    $this->values['title'] = $title;

    return $this;
  }

  public function getAbbreviation(): string {
    return $this->values['abbreviation'];
  }

  public function setAbbreviation(string $abbreviation): self {
    $this->values['abbreviation'] = $abbreviation;

    return $this;
  }

  public function getStartDate(): \DateTime {
    return new \DateTime($this->values['start_date']);
  }

  public function setStartDate(\DateTimeInterface $startDate): self {
    $this->values['start_date'] = static::toDateTimeStr($startDate);

    return $this;
  }

  public function getRequestsStartDate(): \DateTime {
    return new \DateTime($this->values['requests_start_date']);
  }

  public function setRequestsStartDate(\DateTimeInterface $requestsStartDate): self {
    $this->values['requests_start_date'] = static::toDateTimeStr($requestsStartDate);

    return $this;
  }

  public function getRequestsEndDate(): \DateTime {
    return new \DateTime($this->values['requests_end_date']);
  }

  public function setRequestsEndDate(\DateTimeInterface $requestsEndDate): self {
    $this->values['requests_end_date'] = static::toDateTimeStr($requestsEndDate);

    return $this;
  }

  public function getCurrency(): string {
    return $this->values['currency'];
  }

  public function setCurrency(string $currency): self {
    $this->values['currency'] = $currency;

    return $this;
  }

  public function getBudget(): ?float {
    return $this->values['budget'];
  }

  public function setBudget(?float $budget): self {
    $this->values['budget'] = $budget;

    return $this;
  }

  /**
   * @phpstan-return array<string>
   */
  public function getPermissions(): array {
    return $this->values['permissions'] ?? [];
  }

}
