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
 *   name: string,
 *   properties: array<string, mixed>,
 * }
 *
 * @phpstan-method fundingCaseTypeT toArray()
 * @phpstan-method void setValues(fundingCaseTypeT $values)
 */
final class FundingCaseTypeEntity extends AbstractEntity {

  /**
   * @phpstan-var fundingCaseTypeT
   */
  protected array $values;

  /**
   * @phpstan-param fundingCaseTypeT $values
   */
  public static function fromArray(array $values): self {
    return new self($values);
  }

  public function getTitle(): string {
    return $this->values['title'];
  }

  public function setTitle(string $title): self {
    $this->values['title'] = $title;

    return $this;
  }

  public function getName(): string {
    return $this->values['name'];
  }

  public function setName(string $name): self {
    $this->values['name'] = $name;

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
