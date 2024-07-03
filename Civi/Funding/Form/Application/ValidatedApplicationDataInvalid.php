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

namespace Civi\Funding\Form\Application;

/**
 * Application data for which the validation failed.
 */
final class ValidatedApplicationDataInvalid implements ValidatedApplicationDataInterface {

  /**
   * @phpstan-var array<string, mixed> JSON serializable.
   *   Request data after JSON schema validation.
   */
  private array $rawData;

  /**
   * @phpstan-param array<string, mixed> $rawData JSON serializable.
   *   Request data after JSON schema validation.
   */
  public function __construct(array $rawData) {
    $this->rawData = $rawData;
  }

  public function getAction(): string {
    return '';
  }

  public function getTitle(): string {
    return '';
  }

  public function getShortDescription(): string {
    return '';
  }

  public function getRecipientContactId(): int {
    return -1;
  }

  public function getStartDate(): ?\DateTimeInterface {
    return NULL;
  }

  public function getEndDate(): ?\DateTimeInterface {
    return NULL;
  }

  public function getAmountRequested(): float {
    return 0.0;
  }

  /**
   * @inheritDoc
   */
  public function getCostItemsData(): array {
    return [];
  }

  /**
   * @inheritDoc
   */
  public function getResourcesItemsData(): array {
    return [];
  }

  /**
   * @inheritDoc
   */
  public function getComment(): ?array {
    return NULL;
  }

  /**
   * @inheritDoc
   */
  public function getMappedData(): array {
    return [];
  }

  /**
   * @inheritDoc
   */
  public function getApplicationData(): array {
    return [];
  }

  /**
   * @inheritDoc
   */
  public function getRawData(): array {
    return $this->rawData;
  }

}
