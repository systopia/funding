<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
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

namespace Civi\Funding\FundingCaseTypes\AuL\SammelantragKurs\FundingCase\Actions;

use Civi\Funding\FundingCase\Actions\FundingCaseSubmitActionsFactoryInterface;

final class KursCaseSubmitActionsFactory implements FundingCaseSubmitActionsFactoryInterface {

  private KursCaseActionsDeterminer $actionsDeterminer;

  private KursCaseSubmitActionsContainer $submitActionsContainer;

  public function __construct(
    KursCaseActionsDeterminer $actionsDeterminer,
    KursCaseSubmitActionsContainer $submitActionsContainer
  ) {
    $this->actionsDeterminer = $actionsDeterminer;
    $this->submitActionsContainer = $submitActionsContainer;
  }

  /**
   * @inheritDoc
   */
  public function createSubmitActions(
    string $fundingCaseStatus,
    array $applicationProcessStatusList,
    array $permissions
  ): array {
    return $this->doCreateSubmitActions(
      $this->actionsDeterminer->getActions($fundingCaseStatus, $applicationProcessStatusList, $permissions)
    );
  }

  /**
   * @inheritDoc
   */
  public function createInitialSubmitActions(array $permissions): array {
    return $this->doCreateSubmitActions($this->actionsDeterminer->getInitialActions($permissions));
  }

  /**
   * @phpstan-param array<string> $actions
   *
   * @phpstan-return array<string, array{label: string, confirm: string|null}>
   *   Map of action names to button labels and confirm messages.
   */
  private function doCreateSubmitActions(array $actions): array {
    /** @phpstan-var \SplPriorityQueue<int, string> $sortedActions */
    $sortedActions = new \SplPriorityQueue();
    foreach ($actions as $action) {
      if ($this->submitActionsContainer->has($action)) {
        $sortedActions->insert($action, $this->submitActionsContainer->getPriority($action));
      }
    }

    $submitActions = [];
    foreach ($sortedActions as $action) {
      $submitActions[$action] = $this->submitActionsContainer->get($action);
    }

    return $submitActions;
  }

}
