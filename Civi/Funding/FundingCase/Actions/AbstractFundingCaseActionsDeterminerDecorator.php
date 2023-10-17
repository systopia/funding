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

/**
 * @phpstan-type statusPermissionsActionMapT array<string|null, array<string, array<string>>>
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
  public function getActions(string $status, array $applicationProcessStatusList, array $permissions): array {
    return $this->actionsDeterminer->getActions($status, $applicationProcessStatusList, $permissions);
  }

  /**
   * @inheritDoc
   */
  public function getInitialActions(array $permissions): array {
    return $this->actionsDeterminer->getInitialActions($permissions);
  }

}
