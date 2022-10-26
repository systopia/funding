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

namespace Civi\Funding\Form;

use Civi\Funding\ApplicationProcess\ActionsDeterminer\ApplicationProcessActionsDeterminerInterface;

final class ApplicationSubmitActionsFactory implements ApplicationSubmitActionsFactoryInterface {

  private ApplicationProcessActionsDeterminerInterface $actionsDeterminer;

  private SubmitActionsContainer $submitActionsContainer;

  public function __construct(
    ApplicationProcessActionsDeterminerInterface $actionsDeterminer,
    SubmitActionsContainer $submitActionsContainer
  ) {
    $this->actionsDeterminer = $actionsDeterminer;
    $this->submitActionsContainer = $submitActionsContainer;
  }

  public function createSubmitActions(string $status, array $permissions): array {
    return $this->doCreateSubmitActions($this->actionsDeterminer->getActions($status, $permissions));
  }

  public function createInitialSubmitActions(array $permissions): array {
    return $this->doCreateSubmitActions($this->actionsDeterminer->getInitialActions($permissions));
  }

  public function isEditAllowed(string $status, array $permissions): bool {
    return $this->actionsDeterminer->isEditAllowed($status, $permissions);
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
      if (!$this->submitActionsContainer->has($action)) {
        throw new \RuntimeException(sprintf('Unknown action "%s"', $action));
      }

      $sortedActions->insert($action, $this->submitActionsContainer->getPriority($action));
    }

    $submitActions = [];
    foreach ($sortedActions as $action) {
      $submitActions[$action] = $this->submitActionsContainer->get($action);
    }

    return $submitActions;
  }

}
