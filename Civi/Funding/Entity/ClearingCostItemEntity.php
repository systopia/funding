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

namespace Civi\Funding\Entity;

/**
 * @phpstan-type clearingCostItemT array{
 *   id?: int,
 *   clearing_process_id: int,
 *   application_cost_item_id: int,
 *   status: string,
 *   file_id: ?int,
 *   receipt_number: ?string,
 *   payment_date: string,
 *   recipient: string,
 *   reason: string,
 *   amount: float,
 *   amount_admitted: ?float,
 * }
 *
 * @phpstan-extends AbstractClearingItemEntity<clearingCostItemT>
 */
final class ClearingCostItemEntity extends AbstractClearingItemEntity {

  public function getApplicationCostItemId(): int {
    return $this->values['application_cost_item_id'];
  }

  public function getFinancePlanItemId(): int {
    return $this->getApplicationCostItemId();
  }

}
