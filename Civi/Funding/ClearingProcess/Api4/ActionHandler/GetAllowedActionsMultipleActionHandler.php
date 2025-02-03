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

namespace Civi\Funding\ClearingProcess\Api4\ActionHandler;

use Civi\Funding\Api4\Action\FundingClearingProcess\GetAllowedActionsMultipleAction;
use Civi\Funding\ClearingProcess\ClearingActionsDeterminer;
use Civi\Funding\ClearingProcess\ClearingProcessBundleLoader;
use Civi\RemoteTools\ActionHandler\ActionHandlerInterface;

final class GetAllowedActionsMultipleActionHandler implements ActionHandlerInterface {

  public const ENTITY_NAME = 'FundingClearingProcess';

  private ClearingActionsDeterminer $actionDeterminer;

  private ClearingProcessBundleLoader $clearingProcessBundleLoader;

  public function __construct(
    ClearingActionsDeterminer $actionDeterminer,
    ClearingProcessBundleLoader $clearingProcessBundleLoader
  ) {
    $this->actionDeterminer = $actionDeterminer;
    $this->clearingProcessBundleLoader = $clearingProcessBundleLoader;
  }

  /**
   * @phpstan-return array<int, array<string, array{label: string, confirm: string|null}>>
   *   Map of action names to button labels and confirm messages indexed by
   *   clearing process ID. Contains only those actions that can be applied
   *   without form data.
   */
  public function getAllowedActionsMultiple(GetAllowedActionsMultipleAction $action): array {
    $actions = [];
    foreach ($action->getIds() as $id) {
      $actions[$id] = $this->getAllowedActionsById($id);
    }

    return $actions;
  }

  /**
   * @phpstan-return array<string, array{label: string, confirm: string|null}>
   *    Map of action names to button labels and confirm messages.
   */
  private function getAllowedActionsById(int $id): array {
    $clearingProcessBundle = $this->clearingProcessBundleLoader->get($id);
    if (NULL === $clearingProcessBundle) {
      return [];
    }

    $actions = [];
    foreach ($this->actionDeterminer->getActions($clearingProcessBundle) as $action => $label) {
      if ('add-comment' !== $action && !$this->actionDeterminer->isEditAction($action)) {
        $actions[$action] = ['label' => $label, 'confirm' => NULL];
      }
    }

    return $actions;
  }

}
