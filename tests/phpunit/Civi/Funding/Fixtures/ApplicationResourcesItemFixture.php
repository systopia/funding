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

use Civi\Api4\FundingApplicationResourcesItem;
use Civi\Funding\Entity\ApplicationResourcesItemEntity;

final class ApplicationResourcesItemFixture {

  /**
   * @phpstan-param array<string, mixed> $values
   *
   * @throws \CRM_Core_Exception
   */
  public static function addFixture(int $applicationProcessId, array $values = []): ApplicationResourcesItemEntity {
    $result = FundingApplicationResourcesItem::create(FALSE)
      ->setValues($values + [
        'application_process_id' => $applicationProcessId,
        'identifier' => 'test',
        'type' => 'TestType',
        'amount' => 1.2,
        'properties' => [],
      ])->execute();

    return ApplicationResourcesItemEntity::singleFromApiResult($result);
  }

}
