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

namespace Civi\Funding\ApplicationProcess\Command;

use Civi\Funding\Entity\ApplicationProcessEntityBundle;
use Civi\Funding\Entity\Traits\ApplicationProcessEntityBundleTrait;

final class ApplicationResourcesItemsPersistCommand {

  use ApplicationProcessEntityBundleTrait;

  /**
   * @phpstan-var array<\Civi\Funding\ApplicationProcess\JsonSchema\ResourcesItem\ResourcesItemData>
   */
  private array $resourcesItemsData;

  /**
   * phpcs:disable Generic.Files.LineLength.TooLong
   *
   * @phpstan-param array<\Civi\Funding\ApplicationProcess\JsonSchema\ResourcesItem\ResourcesItemData> $resourcesItemsData
   *
   * phpcs:enable
   */
  public function __construct(
    ApplicationProcessEntityBundle $applicationProcessBundle,
    array $resourcesItemsData
  ) {
    $this->applicationProcessBundle = $applicationProcessBundle;
    $this->resourcesItemsData = $resourcesItemsData;
  }

  /**
   * @phpstan-return array<string, mixed>
   */
  public function getRequestData(): array {
    return $this->getApplicationProcess()->getRequestData();
  }

  /**
   * @phpstan-return array<\Civi\Funding\ApplicationProcess\JsonSchema\ResourcesItem\ResourcesItemData>
   */
  public function getResourcesItemsData(): array {
    return $this->resourcesItemsData;
  }

}
