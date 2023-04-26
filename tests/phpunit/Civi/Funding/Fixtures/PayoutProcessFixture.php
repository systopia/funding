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

use Civi\Api4\FundingPayoutProcess;
use Civi\Funding\Entity\PayoutProcessEntity;

final class PayoutProcessFixture {

  /**
   * @phpstan-param array<string, mixed> $values
   *
   * @throws \CRM_Core_Exception
   */
  public static function addFixture(int $fundingCaseId, array $values = []): PayoutProcessEntity {
    $result = FundingPayoutProcess::create(FALSE)
      ->setValues($values + [
        'funding_case_id' => $fundingCaseId,
        'status' => 'new',
        'amount_total' => 1.2,
      ])->execute();

    return PayoutProcessEntity::singleFromApiResult($result);
  }

}
