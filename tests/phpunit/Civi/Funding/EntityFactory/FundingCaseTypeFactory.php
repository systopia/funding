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

namespace Civi\Funding\EntityFactory;

use Civi\Funding\Entity\FundingCaseTypeEntity;

/**
 * @phpstan-type fundingCaseTypeValuesT array{
 *   id?: int,
 *   title?: string,
 *   name?: string,
 *   properties?: array<string, mixed>,
 * }
 */
final class FundingCaseTypeFactory {

  public const DEFAULT_ID = 5;

  public const DEFAULT_NAME = 'TestFundingCaseType';

  /**
   * @phpstan-param fundingCaseTypeValuesT $values
   */
  public static function createFundingCaseType(array $values = []): FundingCaseTypeEntity {
    return FundingCaseTypeEntity::fromArray($values + [
      'id' => self::DEFAULT_ID,
      'title' => 'Test Funding Case Type',
      'abbreviation' => 'TFCT',
      'name' => self::DEFAULT_NAME,
      'is_combined_application' => TRUE,
      'application_process_label' => 'Test',
      'properties' => [],
    ]);
  }

}
