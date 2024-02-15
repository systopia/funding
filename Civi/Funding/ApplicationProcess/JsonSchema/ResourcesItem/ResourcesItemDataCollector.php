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

namespace Civi\Funding\ApplicationProcess\JsonSchema\ResourcesItem;

final class ResourcesItemDataCollector {

  /**
   * @phpstan-var array<string, ResourcesItemData>
   *   ResourcesItemData mapped by identifier.
   */
  private array $resourcesItemsData = [];

  public function addResourcesItemData(ResourcesItemData $resourcesItemData): self {
    if ($this->hasIdentifier($resourcesItemData->getIdentifier())) {
      throw new \RuntimeException(sprintf(
        'Duplicate resources item data identifier "%s" at "%s"',
        $resourcesItemData->getIdentifier(),
        $resourcesItemData->getDataPointer(),
      ));
    }

    $this->resourcesItemsData[$resourcesItemData->getIdentifier()] = $resourcesItemData;

    return $this;
  }

  /**
   * @phpstan-return array<string, ResourcesItemData>
   *    ResourcesItemData mapped by identifier.
   */
  public function getResourcesItemsData(): array {
    return $this->resourcesItemsData;
  }

  public function hasIdentifier(string $identifier): bool {
    return isset($this->resourcesItemsData[$identifier]);
  }

}
