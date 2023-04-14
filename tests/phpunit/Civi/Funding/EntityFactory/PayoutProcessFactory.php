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

namespace Civi\Funding\EntityFactory;

use Civi\Funding\Entity\PayoutProcessEntity;

/**
 * @phpstan-type payoutProcessT array{
 *   id?: int,
 *   funding_case_id?: int,
 *   status?: string,
 *   amount_total?: float,
 * }
 */
final class PayoutProcessFactory {

  public const DEFAULT_ID = 6;

  /**
   * @phpstan-param payoutProcessT $values
   */
  public static function create(array $values = []): PayoutProcessEntity {
    return PayoutProcessEntity::fromArray($values + [
      'id' => self::DEFAULT_ID,
      'funding_case_id' => FundingCaseFactory::DEFAULT_ID,
      'status' => 'open',
      'amount_total' => 12.34,
      'amount_paid_out' => 0.0,
    ]);
  }

}
