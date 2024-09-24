<?php
/*
 * Copyright (C) 2022 SYSTOPIA GmbH
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

use Civi\Api4\FundingCaseType;
use Civi\Funding\Entity\FundingCaseTypeEntity;

final class FundingCaseTypeFixture {

  /**
   * @param array<string, mixed> $values
   *
   * @throws \CRM_Core_Exception
   */
  public static function addFixture(array $values = []): FundingCaseTypeEntity {
    $result = FundingCaseType::create(FALSE)
      ->setValues($values + [
        'title' => 'Test Case Type',
        'abbreviation' => 'TCT',
        'name' => 'TestCaseType',
        'is_combined_application' => TRUE,
      ])->execute();

    return FundingCaseTypeEntity::singleFromApiResult($result);
  }

}
