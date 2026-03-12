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

namespace Civi\Funding\FundingCase\Actions;

use Civi\Funding\Entity\FundingCaseBundle;
use Civi\Funding\Entity\FundingCaseTypeEntity;

/**
 * @phpstan-type statusPermissionsActionMapT array<string|null, array<string, list<string>>>
 *
 * @codeCoverageIgnore
 */
abstract class AbstractFundingCaseActionsDeterminerDecorator extends AbstractFundingCaseActionsDeterminer {

  protected FundingCaseActionsDeterminerInterface $actionsDeterminer;

  public function __construct(FundingCaseActionsDeterminerInterface $actionsDeterminer) {
    $this->actionsDeterminer = $actionsDeterminer;
  }

  /**
   * @inheritDoc
   */
  public function getActions(FundingCaseBundle $fundingCaseBundle, array $applicationProcessStatusList): array {
    return $this->actionsDeterminer->getActions($fundingCaseBundle, $applicationProcessStatusList);
  }

  /**
   * @inheritDoc
   */
  public function getInitialActions(FundingCaseTypeEntity $fundingCaseType, array $permissions): array {
    return $this->actionsDeterminer->getInitialActions($fundingCaseType, $permissions);
  }

}
