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

namespace Civi\Funding\ApplicationProcess\Api4\ActionHandler;

use Civi\Funding\Api4\Action\FundingApplicationProcess\GetAllowedActionsMultipleAction;
use Civi\Funding\ApplicationProcess\ApplicationProcessBundleLoader;
use Civi\Funding\ApplicationProcess\Command\ApplicationAllowedActionsGetCommand;
use Civi\Funding\ApplicationProcess\Handler\ApplicationAllowedActionsGetHandlerInterface;
use Civi\RemoteTools\ActionHandler\ActionHandlerInterface;

final class GetAllowedActionsMultipleActionHandler implements ActionHandlerInterface {

  public const ENTITY_NAME = 'FundingApplicationProcess';

  private ApplicationAllowedActionsGetHandlerInterface $allowedActionsGetHandler;

  private ApplicationProcessBundleLoader $applicationProcessBundleLoader;

  public function __construct(
    ApplicationAllowedActionsGetHandlerInterface $allowedActionsGetHandler,
    ApplicationProcessBundleLoader $applicationProcessBundleLoader
  ) {
    $this->allowedActionsGetHandler = $allowedActionsGetHandler;
    $this->applicationProcessBundleLoader = $applicationProcessBundleLoader;
  }

  /**
   * @return array<int, array<string, array{label: string, confirm: string|null}>>
   *   Map of action names to button labels and confirm messages indexed by
   *   application process ID. Contains only those actions that can be applied to
   *   multiple actions at the same time, i.e. can be performed without form data.
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
    $applicationProcessBundle = $this->applicationProcessBundleLoader->get($id);
    if (NULL === $applicationProcessBundle) {
      return [];
    }

    $actions = $this->allowedActionsGetHandler->handle(new ApplicationAllowedActionsGetCommand(
      $applicationProcessBundle,
      $this->applicationProcessBundleLoader->getStatusList($applicationProcessBundle),
    ));

    $result = [];
    foreach ($actions as $action) {
      if ($action->isBatchPossible()) {
        $result[$action->getName()] = [
          'label' => $action->getLabel(),
          'confirm' => $action->getConfirmMessage(),
        ];
      }
    }

    return $result;
  }

}
