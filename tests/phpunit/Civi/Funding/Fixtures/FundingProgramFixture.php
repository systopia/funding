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

use Civi\Api4\FundingProgram;
use Civi\Funding\Entity\FundingProgramEntity;

final class FundingProgramFixture {

  public const DEFAULT_CURRENCY = 'EUR';

  /**
   * @param array<string, scalar|null> $values
   *
   * @throws \CRM_Core_Exception
   */
  public static function addFixture(array $values = []): FundingProgramEntity {
    static $count = 1;

    $result = FundingProgram::create(FALSE)
      ->setValues($values + [
        'title' => 'TestFundingProgram' . $count,
        'abbreviation' => 'TFP' . $count,
        'identifier_prefix' => 'TFP' . $count . '-',
        'start_date' => '2022-10-22',
        'end_date' => '2023-10-22',
        'requests_start_date' => '2022-06-22',
        'requests_end_date' => '2022-12-31',
        'budget' => NULL,
        'currency' => self::DEFAULT_CURRENCY,
      ])->execute();

    ++$count;

    return FundingProgramEntity::singleFromApiResult($result);
  }

}
