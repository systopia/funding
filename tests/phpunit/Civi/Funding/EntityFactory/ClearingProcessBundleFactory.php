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

use Civi\Funding\Entity\ClearingProcessEntityBundle;

/**
 * @phpstan-import-type clearingProcessT from \Civi\Funding\EntityFactory\ClearingProcessFactory
 * @phpstan-import-type applicationProcessValuesT from \Civi\Funding\EntityFactory\ApplicationProcessBundleFactory
 * @phpstan-import-type fundingCaseValuesT from \Civi\Funding\EntityFactory\ApplicationProcessBundleFactory
 * @phpstan-import-type fundingCaseTypeValuesT from \Civi\Funding\EntityFactory\ApplicationProcessBundleFactory
 * @phpstan-import-type fundingProgramValuesT from \Civi\Funding\EntityFactory\ApplicationProcessBundleFactory
 */
final class ClearingProcessBundleFactory {

  /**
   * @phpstan-param clearingProcessT $clearingProcessValues
   * @phpstan-param applicationProcessValuesT $applicationProcessValues
   * @phpstan-param fundingCaseValuesT $fundingCaseValues
   * @phpstan-param fundingCaseTypeValuesT $fundingCaseTypeValues
   * @phpstan-param fundingProgramValuesT $fundingProgramValues
   */
  public static function create(
    array $clearingProcessValues = [],
    array $applicationProcessValues = [],
    array $fundingCaseValues = [],
    array $fundingCaseTypeValues = [],
    array $fundingProgramValues = []
  ): ClearingProcessEntityBundle {
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle(
      $applicationProcessValues,
      $fundingCaseValues,
      $fundingCaseTypeValues,
      $fundingProgramValues
    );

    return new ClearingProcessEntityBundle(
      ClearingProcessFactory::create($clearingProcessValues),
      $applicationProcessBundle
    );
  }

}
