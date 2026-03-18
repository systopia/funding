<?php
/*
 * Copyright (C) 2026 SYSTOPIA GmbH
 *
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License as published by the Free
 * Software Foundation, either version 3 of the License, or (at your option) any
 * later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types = 1);

namespace Civi\Funding\FundingCase\Actions;

use Civi\Funding\Entity\FundingCaseBundle;
use Civi\Funding\Entity\FundingCaseTypeEntity;
use Civi\Funding\FundingCaseType\AbstractFundingCaseTypeServiceCollector;

/**
 * @extends AbstractFundingCaseTypeServiceCollector<FundingCaseActionsDeterminerInterface>
 */
// phpcs:ignore Generic.Files.LineLength.TooLong
final class FundingCaseActionsDeterminerCollector extends AbstractFundingCaseTypeServiceCollector implements FundingCaseActionsDeterminerInterface {

  /**
   * @inheritDoc
   */
  public function getActions(FundingCaseBundle $fundingCaseBundle, array $applicationProcessStatusList): array {
    return $this
      ->getService($fundingCaseBundle->getFundingCaseType()->getName())
      ->getActions($fundingCaseBundle, $applicationProcessStatusList);
  }

  /**
   * @inheritDoc
   */
  public function getInitialActions(FundingCaseTypeEntity $fundingCaseType, array $permissions): array {
    return $this->getService($fundingCaseType->getName())->getInitialActions($fundingCaseType, $permissions);
  }

  /**
   * @inheritDoc
   */
  public function isActionAllowed(
    string $action,
    FundingCaseBundle $fundingCaseBundle,
    array $applicationProcessStatusList,
  ): bool {
    return $this
      ->getService($fundingCaseBundle->getFundingCaseType()->getName())
      ->isActionAllowed($action, $fundingCaseBundle, $applicationProcessStatusList);
  }

}
