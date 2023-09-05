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
 * @phpstan-type fundingCaseTypeT array{
 *   id?: int,
 *   title: string,
 *   abbreviation: string,
 *   name: string,
 *   is_combined_application: bool,
 *   application_process_label: string|null,
 *   properties: array<string, mixed>,
 * }
 *
 * @phpstan-extends AbstractEntity<fundingCaseTypeT>
 */
final class FundingCaseTypeEntity extends AbstractEntity {

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

  public function getName(): string {
    return $this->values['name'];
  }

  public function setName(string $name): self {
    $this->values['name'] = $name;

    return $this;
  }

  public function getIsCombinedApplication(): bool {
    return $this->values['is_combined_application'];
  }

  public function setIsCombinedApplication(bool $isCombinedApplication): self {
    $this->values['is_combined_application'] = $isCombinedApplication;

    return $this;
  }

  public function getApplicationProcessLabel(): ?string {
    return $this->values['application_process_label'];
  }

  public function setApplicationProcessLabel(?string $applicationProcessLabel): self {
    $this->values['application_process_label'] = $applicationProcessLabel;

    return $this;
  }

  /**
   * @phpstan-return array<string, mixed>
   *   JSON serializable array.
   */
  public function getProperties(): array {
    return $this->values['properties'];
  }

  /**
   * @phpstan-param array<string, mixed> $properties
   *   JSON serializable array.
   */
  public function setProperties(array $properties): self {
    $this->values['properties'] = $properties;

    return $this;
  }

}
