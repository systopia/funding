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

namespace Civi\Funding\Mock\Form\FundingCaseType;

use Civi\Funding\Form\ValidatedApplicationDataInterface;

/**
 * @phpstan-type testValidatedDataT array<string, mixed>&array{
 *   action: string,
 *   title: string,
 *   shortDescription: string,
 *   recipient: int,
 *   startDate: string,
 *   endDate: string,
 *   amountRequested: float,
 *   comment?: string,
 * }
 */
final class TestValidatedData implements ValidatedApplicationDataInterface {

  /**
   * @phpstan-var testValidatedDataT
   */
  private array $data;

  /**
   * @phpstan-param array<string, mixed> $validatedData
   */
  public function __construct(array $validatedData) {
    /** @phpstan-var testValidatedDataT $validatedData */
    $this->data = $validatedData;
  }

  public function getAction(): string {
    return $this->data['action'];
  }

  public function getTitle(): string {
    return $this->data['title'];
  }

  public function getShortDescription(): string {
    return $this->data['shortDescription'];
  }

  public function getRecipientContactId(): int {
    return $this->data['recipient'];
  }

  public function getStartDate(): \DateTimeInterface {
    return new \DateTime($this->data['startDate']);
  }

  public function getEndDate(): \DateTimeInterface {
    return new \DateTime($this->data['endDate']);
  }

  public function getAmountRequested(): float {
    return $this->data['amountRequested'];
  }

  public function getComment(): ?string {
    return $this->data['comment'] ?? NULL;
  }

  public function getApplicationData(): array {
    $data = $this->data;
    unset($data['action']);

    return $data;
  }

}
