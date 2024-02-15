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

namespace Civi\Funding\ApplicationProcess\JsonSchema\CostItem;

/**
 * @phpstan-type costItemDataT array{
 *   type: string,
 *   identifier: string,
 *   amount: float,
 *   properties: array<string, mixed>,
 *   clearing: array{itemLabel: string}|null,
 *   dataPointer: string,
 *   dataType: 'number'|'integer'|'object',
 * }
 *
 * @codeCoverageIgnore
 */
final class CostItemData {

  /**
   * @phpstan-var costItemDataT
   */
  private array $data;

  /**
   * @phpstan-param costItemDataT $data
   */
  public function __construct(array $data) {
    $this->data = $data;
  }

  public function getType(): string {
    return $this->data['type'];
  }

  public function getIdentifier(): string {
    return $this->data['identifier'];
  }

  public function getAmount(): float {
    return $this->data['amount'];
  }

  /**
   * @phpstan-return array<string, mixed>
   */
  public function getProperties(): array {
    return $this->data['properties'];
  }

  /**
   * @phpstan-return array{itemLabel: string}|null
   *   NULL if no clearing is required.
   */
  public function getClearing(): ?array {
    return $this->data['clearing'];
  }

  public function getDataPointer(): string {
    return $this->data['dataPointer'];
  }

  /**
   * @phpstan-return 'number'|'integer'|'object'
   */
  public function getDataType(): string {
    return $this->data['dataType'];
  }

}
