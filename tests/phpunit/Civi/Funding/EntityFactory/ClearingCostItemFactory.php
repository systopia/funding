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

namespace Civi\Funding\EntityFactory;

use Civi\Funding\Entity\ClearingCostItemEntity;

/**
 * @phpstan-type clearingCostItemT array{
 *   id?: int,
 *   clearing_process_id?: int,
 *   application_cost_item_id?: int,
 *   status?: string,
 *   file_id?: ?int,
 *   receipt_number?: ?string,
 *   receipt_date?: ?string,
 *   payment_date?: string,
 *   recipient?: string,
 *   reason?: string,
 *   amount?: float,
 *   amount_admitted?: ?float,
 * }
 */
final class ClearingCostItemFactory {

  /**
   * @phpstan-param clearingCostItemT $values
   */
  public static function create(array $values = []): ClearingCostItemEntity {
    $values += [
      'clearing_process_id' => ClearingProcessFactory::DEFAULT_ID,
      'application_cost_item_id' => ApplicationCostItemFactory::DEFAULT_ID,
      'status' => 'new',
      'file_id' => NULL,
      'receipt_number' => NULL,
      'receipt_date' => NULL,
      'payment_date' => '2024-04-04',
      'recipient' => 'costRecipient',
      'reason' => 'TestClearingCostItem',
      'amount' => 1.23,
      'amount_admitted' => NULL,
    ];

    return ClearingCostItemEntity::fromArray($values)->reformatDates();
  }

}
