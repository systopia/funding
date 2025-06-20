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

namespace Civi\Funding\ClearingProcess\Handler;

use Civi\API\Exception\UnauthorizedException;
use Civi\Funding\ClearingProcess\ClearingActionsDeterminer;
use Civi\Funding\ClearingProcess\ClearingProcessManager;
use Civi\Funding\ClearingProcess\ClearingStatusDeterminer;
use Civi\Funding\ClearingProcess\Command\ClearingActionApplyCommand;

/**
 * @phpstan-import-type clearingFormDataT from \Civi\Funding\ClearingProcess\Form\ClearingFormGenerator
 */
final class ClearingActionApplyHandler implements ClearingActionApplyHandlerInterface {

  private ClearingActionsDeterminer $actionsDeterminer;

  private ClearingProcessManager $clearingProcessManager;

  private ClearingStatusDeterminer $statusDeterminer;

  public function __construct(
    ClearingActionsDeterminer $actionsDeterminer,
    ClearingProcessManager $clearingProcessManager,
    ClearingStatusDeterminer $statusDeterminer
  ) {
    $this->actionsDeterminer = $actionsDeterminer;
    $this->clearingProcessManager = $clearingProcessManager;
    $this->statusDeterminer = $statusDeterminer;
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function handle(ClearingActionApplyCommand $command): void {
    if (!$this->actionsDeterminer->isActionAllowed($command->getAction(), $command->getClearingProcessBundle())) {
      throw new UnauthorizedException(sprintf(
        'Action "%s" is not allowed on clearing process with ID %d',
        $command->getAction(),
        $command->getClearingProcessBundle()->getClearingProcess()->getId()
      ));
    }

    $clearingProcessBundle = $command->getClearingProcessBundle();
    $clearingProcess = $clearingProcessBundle->getClearingProcess();

    if ('add-comment' !== $command->getAction()) {
      $clearingProcess->setFullStatus(
        $this->statusDeterminer->getStatus($clearingProcess->getFullStatus(), $command->getAction())
      );
      $this->clearingProcessManager->update($clearingProcessBundle);
    }
  }

}
