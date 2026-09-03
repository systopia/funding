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

namespace Civi\Funding\ApplicationProcess\Api4\ActionHandler;

use Civi\Funding\Api4\Action\FundingApplicationProcess\GetAllowedActionNamesAction;
use Civi\Funding\ApplicationProcess\ActionsDeterminer\ApplicationProcessActionsDeterminerInterface;
use Civi\Funding\ApplicationProcess\ApplicationProcessManager;
use Civi\RemoteTools\ActionHandler\ActionHandlerInterface;

final class GetAllowedActionsNamesActionHandler implements ActionHandlerInterface {

  public const ENTITY_NAME = 'FundingApplicationProcess';

  public function __construct(
    private readonly ApplicationProcessActionsDeterminerInterface $actionsDeterminer,
    private readonly ApplicationProcessManager $applicationProcessManager,
  ) {}

  /**
   * @return array<int, list<string>>
   *   Mapping of application process ID to list of action names.
   */
  public function getAllowedActionNames(GetAllowedActionNamesAction $action): array {
    $actionNames = [];
    foreach ($action->getIds() as $id) {
      $actionNames[$id] = $this->getAllowedActionNamesById($id);
    }

    return $actionNames;
  }

  /**
   * @return list<string>
   */
  private function getAllowedActionNamesById(int $applicationProcessId): array {
    $applicationProcessBundle = $this->applicationProcessManager->getBundle($applicationProcessId);
    if (NULL === $applicationProcessBundle) {
      return [];
    }

    return $this->actionsDeterminer->getActions(
      $applicationProcessBundle,
      $this->applicationProcessManager->getStatusList($applicationProcessBundle)
    );
  }

}
