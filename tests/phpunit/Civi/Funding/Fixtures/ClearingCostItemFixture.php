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

namespace Civi\Funding\Fixtures;

use Civi\Api4\FundingClearingCostItem;
use Civi\Funding\Entity\ClearingCostItemEntity;

final class ClearingCostItemFixture {

  /**
   * @phpstan-param array<string, mixed> $values
   *
   * @throws \CRM_Core_Exception
   */
  public static function addFixture(
    int $clearingProcessId,
    int $applicationCostItemId,
    array $values = []
  ): ClearingCostItemEntity {
    $result = FundingClearingCostItem::create(FALSE)
      ->setValues($values + [
        'clearing_process_id' => $clearingProcessId,
        'application_cost_item_id' => $applicationCostItemId,
        'status' => 'new',
        'file_id' => NULL,
        'amount' => 1.2,
        'amount_admitted' => NULL,
        'description' => 'Test Clearing Cost Item',
      ])->execute();

    return ClearingCostItemEntity::singleFromApiResult($result);
  }

}
