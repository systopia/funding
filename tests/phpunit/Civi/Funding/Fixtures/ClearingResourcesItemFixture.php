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

namespace Civi\Funding\Fixtures;

use Civi\Api4\FundingClearingResourcesItem;
use Civi\Funding\Entity\ClearingResourcesItemEntity;

final class ClearingResourcesItemFixture {

  /**
   * @phpstan-param array<string, mixed> $values
   *
   * @throws \CRM_Core_Exception
   */
  public static function addFixture(
    int $clearingProcessId,
    int $applicationResourcesItemId,
    array $values = []
  ): ClearingResourcesItemEntity {
    $result = FundingClearingResourcesItem::create(FALSE)
      ->setValues($values + [
        'clearing_process_id' => $clearingProcessId,
        'app_resources_item_id' => $applicationResourcesItemId,
        'status' => 'new',
        'file_id' => NULL,
        'receipt_number' => NULL,
        'receipt_date' => NULL,
        'payment_date' => '2024-04-04',
        'recipient' => 'Recipient',
        'reason' => 'Test Clearing Resources Item',
        'amount' => 1.2,
        'amount_admitted' => NULL,
      ])->execute();

    return ClearingResourcesItemEntity::singleFromApiResult($result)->reformatDates();
  }

}
