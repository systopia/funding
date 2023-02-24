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

/**
 * @phpstan-type fundingProgramT array{
 *   id: int,
 *   title: string,
 *   abbreviation: string,
 *   start_date: string,
 *   end_date: string,
 *   requests_start_date: string,
 *   requests_end_date: string,
 *   currency: string,
 *   budget: float|null,
 * }
 */
final class FundingProgramFixture {

  /**
   * @param array<string, scalar|null> $values
   *
   * @throws \API_Exception
   */
  public static function addFixture(array $values = []): FundingProgramEntity {
    /** @phpstan-var fundingProgramT $fundingProgramValues */
    $fundingProgramValues = FundingProgram::create()
      ->setCheckPermissions(FALSE)
      ->setValues($values + [
        'title' => 'TestFundingProgram',
        'abbreviation' => 'TFP',
        'start_date' => '2022-10-22',
        'end_date' => '2023-10-22',
        'requests_start_date' => '2022-06-22',
        'requests_end_date' => '2022-12-31',
        'budget' => NULL,
        'currency' => '€',
      ])->execute()->first();

    return FundingProgramEntity::fromArray($fundingProgramValues);
  }

}
