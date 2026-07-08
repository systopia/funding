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

namespace Civi\Funding\ApplicationProcess\ActionsDeterminer;

use Civi\Funding\Entity\ApplicationProcessEntityBundle;
use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\Entity\FundingCaseTypeEntity;
use Civi\Funding\FundingCaseType\AbstractFundingCaseTypeServiceCollector;

/**
 * @extends AbstractFundingCaseTypeServiceCollector<ApplicationProcessActionsDeterminerInterface>
 */
// phpcs:ignore Generic.Files.LineLength.TooLong
final class ApplicationProcessActionsDeterminerCollector extends AbstractFundingCaseTypeServiceCollector implements ApplicationProcessActionsDeterminerInterface {

  /**
   * @inheritDoc
   */
  public function getActions(ApplicationProcessEntityBundle $applicationProcessBundle, array $statusList): array {
    return $this
      ->getDeterminer($applicationProcessBundle->getFundingCaseType())
      ->getActions($applicationProcessBundle, $statusList);
  }

  /**
   * @inheritDoc
   */
  public function getInitialActions(
    array $permissions,
    FundingCaseTypeEntity $fundingCaseType,
    ?FundingCaseEntity $fundingCase
  ): array {
    return $this
      ->getDeterminer($fundingCaseType)
      ->getInitialActions($permissions, $fundingCaseType, $fundingCase);
  }

  /**
   * @inheritDoc
   */
  public function isActionAllowed(
    string $action,
    ApplicationProcessEntityBundle $applicationProcessBundle,
    array $statusList
  ): bool {
    return $this
      ->getDeterminer($applicationProcessBundle->getFundingCaseType())
      ->isActionAllowed($action, $applicationProcessBundle, $statusList);
  }

  /**
   * @inheritDoc
   */
  public function isAnyActionAllowed(
    array $actions,
    ApplicationProcessEntityBundle $applicationProcessBundle,
    array $statusList
  ): bool {
    return $this
      ->getDeterminer($applicationProcessBundle->getFundingCaseType())
      ->isAnyActionAllowed($actions, $applicationProcessBundle, $statusList);
  }

  /**
   * @inheritDoc
   */
  public function isEditAllowed(ApplicationProcessEntityBundle $applicationProcessBundle, array $statusList): bool {
    return $this
      ->getDeterminer($applicationProcessBundle->getFundingCaseType())
      ->isEditAllowed($applicationProcessBundle, $statusList);
  }

  private function getDeterminer(FundingCaseTypeEntity $fundingCaseType): ApplicationProcessActionsDeterminerInterface {
    return $this->getService($fundingCaseType->getName());
  }

}
