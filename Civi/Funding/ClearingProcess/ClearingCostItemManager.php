<?php
/*
 * Copyright (C) 2024 SYSTOPIA GmbH
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

namespace Civi\Funding\ClearingProcess;

use Civi\Api4\FundingClearingCostItem;
use Civi\Funding\Entity\ClearingCostItemEntity;
use Civi\RemoteTools\Api4\Query\Comparison;

/**
 * @extends AbstractClearingItemManager<ClearingCostItemEntity>
 */
final class ClearingCostItemManager extends AbstractClearingItemManager {

  /**
   * @inheritDoc
   */
  public function getByApplicationProcessId(int $applicationProcessId): array {
    $result = $this->api4->getEntities(
      $this->getApiEntityName(),
      Comparison::new('application_cost_item_id.application_process_id', '=', $applicationProcessId)
    )->indexBy('id');

    // @phpstan-ignore-next-line
    return ClearingCostItemEntity::allFromApiResult($result);
  }

  /**
   * @phpstan-return array<int, ClearingCostItemEntity>
   *   Clearing items indexed by id.
   *
   * @throws \CRM_Core_Exception
   */
  public function getByCostItemId(int $costItemId): array {
    $result = $this->api4->getEntities(
      $this->getApiEntityName(),
      Comparison::new('application_cost_item_id', '=', $costItemId)
    )->indexBy('id');

    // @phpstan-ignore-next-line
    return ClearingCostItemEntity::allFromApiResult($result);
  }

  protected function getApiEntityName(): string {
    return FundingClearingCostItem::getEntityName();
  }

  /**
   * @inheritDoc
   */
  protected function getEntityClass(): string {
    return ClearingCostItemEntity::class;
  }

}
