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

use Civi\Api4\FundingApplicationCostItem;
use Civi\Funding\Entity\ApplicationCostItemEntity;

final class ApplicationCostItemFixture {

  private static int $count = 0;

  /**
   * @phpstan-param array<string, mixed> $values
   *
   * @throws \CRM_Core_Exception
   */
  public static function addFixture(int $applicationProcessId, array $values = []): ApplicationCostItemEntity {
    self::$count++;
    $identifier = $values['identifier'] ?? ('amountRequested' . self::$count);

    $result = FundingApplicationCostItem::create(FALSE)
      ->setValues($values + [
        'application_process_id' => $applicationProcessId,
        'identifier' => 'amountRequested' . self::$count,
        'type' => 'amount',
        'amount' => 1.2,
        'data_pointer' => '/' . $identifier,
        'properties' => [],
      ])->execute();

    return ApplicationCostItemEntity::singleFromApiResult($result);
  }

}
