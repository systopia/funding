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

use Civi\Funding\Entity\FundingCaseBundle;

/**
 * @phpstan-import-type fundingCaseValuesT from \Civi\Funding\EntityFactory\FundingCaseFactory
 * @phpstan-import-type fundingCaseTypeValuesT from \Civi\Funding\EntityFactory\FundingCaseTypeFactory
 * @phpstan-import-type fundingProgramValuesT from \Civi\Funding\EntityFactory\FundingProgramFactory
 */
final class FundingCaseBundleFactory {

  /**
   * @phpstan-param fundingCaseValuesT $fundingCaseValues
   * @phpstan-param fundingCaseTypeValuesT $fundingCaseTypeValues
   * @phpstan-param fundingProgramValuesT $fundingProgramValues
   */
  public static function create(
    array $fundingCaseValues = [],
    array $fundingCaseTypeValues = [],
    array $fundingProgramValues = []
  ): FundingCaseBundle {
    return new FundingCaseBundle(
      FundingCaseFactory::createFundingCase($fundingCaseValues),
      FundingCaseTypeFactory::createFundingCaseType($fundingCaseTypeValues),
      FundingProgramFactory::createFundingProgram($fundingProgramValues),
    );
  }

}
