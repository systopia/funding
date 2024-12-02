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

use Civi\Funding\Entity\PayoutProcessBundle;

/**
 * @phpstan-import-type payoutProcessT from \Civi\Funding\EntityFactory\PayoutProcessFactory
 * @phpstan-import-type fundingCaseValuesT from \Civi\Funding\EntityFactory\FundingCaseBundleFactory
 * @phpstan-import-type fundingCaseTypeValuesT from \Civi\Funding\EntityFactory\FundingCaseBundleFactory
 * @phpstan-import-type fundingProgramValuesT from \Civi\Funding\EntityFactory\FundingCaseBundleFactory
 */
final class PayoutProcessBundleFactory {

  /**
   * @phpstan-param payoutProcessT $payoutProcessValues
   * @phpstan-param fundingCaseValuesT $fundingCaseValues
   * @phpstan-param fundingCaseTypeValuesT $fundingCaseTypeValues
   * @phpstan-param fundingProgramValuesT $fundingProgramValues
   */
  public static function create(
    array $payoutProcessValues = [],
    array $fundingCaseValues = [],
    array $fundingCaseTypeValues = [],
    array $fundingProgramValues = []
  ): PayoutProcessBundle {
    $fundingCaseBundle = FundingCaseBundleFactory::create(
      $fundingCaseValues,
      $fundingCaseTypeValues,
      $fundingProgramValues
    );

    return new PayoutProcessBundle(
      PayoutProcessFactory::create($payoutProcessValues),
      $fundingCaseBundle
    );
  }

}
