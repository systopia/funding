<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
 *
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License as published by the Free
 * Software Foundation, either version 3 of the License, or (at your option) any
 * later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types = 1);

namespace Civi\Funding\Upgrade;

use Civi\Api4\FundingClearingCostItem;
use Civi\Api4\FundingClearingResourcesItem;
use Civi\RemoteTools\Api4\Api4Interface;

final class Upgrader0017 implements UpgraderInterface {

  private Api4Interface $api4;

  public function __construct(Api4Interface $api4) {
    $this->api4 = $api4;
  }

  /**
   * @inheritDoc
   */
  public function execute(\Log $log): void {
    $log->info('Add form key to clearing cost items');
    $this->addFormKeyToClearingItems(FundingClearingCostItem::getEntityName(), 'application_cost_item_id');
    $log->info('Add form key to clearing resources items');
    $this->addFormKeyToClearingItems(FundingClearingResourcesItem::getEntityName(), 'app_resources_item_id');
  }

  private function addFormKeyToClearingItems(string $entityName, string $financePlanItemIdFieldName): void {
    /** @var iterable<array<string, int>> $clearingItems */
    $clearingItems = $this->api4->execute($entityName, 'get', [
      'select' => ['id', $financePlanItemIdFieldName],
      'orderBy' => ['id' => 'ASC'],
    ]);

    $recordsCount = [];
    foreach ($clearingItems as $clearingItem) {
      $financePlanItemId = $clearingItem[$financePlanItemIdFieldName];
      $recordsCount[$financePlanItemId] ??= 0;

      $this->api4->updateEntity(
        $entityName,
        $clearingItem['id'],
        ['form_key' => "$financePlanItemId/{$recordsCount[$financePlanItemId]}"]
      );
      $recordsCount[$financePlanItemId]++;
    }
  }

}
