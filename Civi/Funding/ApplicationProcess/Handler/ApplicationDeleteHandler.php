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

namespace Civi\Funding\ApplicationProcess\Handler;

use Civi\API\Exception\UnauthorizedException;
use Civi\Funding\ApplicationProcess\ActionsDeterminer\ApplicationProcessActionsDeterminerInterface;
use Civi\Funding\ApplicationProcess\ApplicationProcessManager;
use Civi\Funding\ApplicationProcess\Command\ApplicationDeleteCommand;
use CRM_Funding_ExtensionUtil as E;

final class ApplicationDeleteHandler implements ApplicationDeleteHandlerInterface {

  private ApplicationProcessActionsDeterminerInterface $actionsDeterminer;

  private ApplicationProcessManager $applicationProcessManager;

  public function __construct(
    ApplicationProcessActionsDeterminerInterface $actionsDeterminer,
    ApplicationProcessManager $applicationProcessManager
  ) {
    $this->actionsDeterminer = $actionsDeterminer;
    $this->applicationProcessManager = $applicationProcessManager;
  }

  public function handle(ApplicationDeleteCommand $command): void {
    if (!$this->isDeleteAllowed($command)) {
      throw new UnauthorizedException(E::ts('Permission to delete application is missing.'));
    }

    $this->applicationProcessManager->delete($command->getApplicationProcessBundle());
  }

  private function isDeleteAllowed(ApplicationDeleteCommand $command): bool {
    return $this->actionsDeterminer->isActionAllowed(
      'delete',
      $command->getApplicationProcess()->getFullStatus(),
      $command->getApplicationProcessStatusList(),
      $command->getFundingCase()->getPermissions()
    );
  }

}
