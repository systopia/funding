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

final class ApplicationCostItemsPersistCommand {

  use ApplicationProcessEntityBundleTrait;

  /**
   * @phpstan-var array<\Civi\Funding\ApplicationProcess\JsonSchema\CostItem\CostItemData>
   */
  private array $costItemsData;

  /**
   * @phpstan-param array<\Civi\Funding\ApplicationProcess\JsonSchema\CostItem\CostItemData> $costItemsData
   */
  public function __construct(
    ApplicationProcessEntityBundle $applicationProcessBundle,
    array $costItemsData
  ) {
    $this->applicationProcessBundle = $applicationProcessBundle;
    $this->costItemsData = $costItemsData;
  }

  /**
   * @phpstan-return array<string, mixed>
   */
  public function getRequestData(): array {
    return $this->getApplicationProcess()->getRequestData();
  }

  /**
   * @phpstan-return array<\Civi\Funding\ApplicationProcess\JsonSchema\CostItem\CostItemData>
   */
  public function getCostItemsData(): array {
    return $this->costItemsData;
  }

}
