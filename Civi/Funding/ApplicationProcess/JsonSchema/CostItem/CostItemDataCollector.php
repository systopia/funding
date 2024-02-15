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

final class CostItemDataCollector {

  /**
   * @phpstan-var array<string, CostItemData>
   *   CostItemData mapped by identifier.
   */
  private array $costItemsData = [];

  public function addCostItemData(CostItemData $costItemData): self {
    if ($this->hasIdentifier($costItemData->getIdentifier())) {
      throw new \RuntimeException(sprintf(
        'Duplicate cost item data identifier "%s" at "%s"',
        $costItemData->getIdentifier(),
        $costItemData->getDataPointer(),
      ));
    }

    $this->costItemsData[$costItemData->getIdentifier()] = $costItemData;

    return $this;
  }

  /**
   * @phpstan-return array<string, CostItemData>
   *    CostItemData mapped by identifier.
   */
  public function getCostItemsData(): array {
    return $this->costItemsData;
  }

  public function hasIdentifier(string $identifier): bool {
    return isset($this->costItemsData[$identifier]);
  }

}
