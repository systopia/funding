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

namespace Civi\Funding\FundingCase\Handler\Helper;

use Civi\Funding\ApplicationProcess\ActionsDeterminer\ApplicationProcessActionsDeterminerInterface;
use Civi\Funding\ApplicationProcess\ApplicationProcessManager;
use Civi\Funding\ApplicationProcess\Command\ApplicationActionApplyCommand;
use Civi\Funding\ApplicationProcess\Handler\ApplicationActionApplyHandlerInterface;
use Civi\Funding\Entity\ApplicationProcessEntityBundle;
use Civi\Funding\Entity\FundingCaseEntity;

class ApplicationAllowedActionApplier {

  private ApplicationActionApplyHandlerInterface $actionApplyHandler;

  private ApplicationProcessActionsDeterminerInterface $actionsDeterminer;

  private ApplicationProcessManager $applicationProcessManager;

  public function __construct(
    ApplicationActionApplyHandlerInterface $actionApplyHandler,
    ApplicationProcessActionsDeterminerInterface $actionsDeterminer,
    ApplicationProcessManager $applicationProcessManager
  ) {
    $this->actionApplyHandler = $actionApplyHandler;
    $this->actionsDeterminer = $actionsDeterminer;
    $this->applicationProcessManager = $applicationProcessManager;
  }

  public function applyAllowedAction(
    int $contactId,
    ApplicationProcessEntityBundle $applicationProcessBundle,
    string $action
  ): void {
    if ($this->isApplicationProcessActionAllowed($action, $applicationProcessBundle)) {
      $this->actionApplyHandler->handle(new ApplicationActionApplyCommand(
        $contactId,
        $action,
        $applicationProcessBundle,
        NULL,
      ));
    }
  }

  public function applyAllowedActionsByFundingCase(
    int $contactId,
    FundingCaseEntity $fundingCase,
    string $action
  ): void {
    $applicationProcessBundles = $this->applicationProcessManager->getBundlesByFundingCaseId($fundingCase->getId());
    foreach ($applicationProcessBundles as $applicationProcessBundle) {
      $this->applyAllowedAction($contactId, $applicationProcessBundle, $action);
    }
  }

  /**
   * @throws \CRM_Core_Exception
   */
  private function isApplicationProcessActionAllowed(
    string $action,
    ApplicationProcessEntityBundle $applicationProcessBundle
  ): bool {
    return $this->actionsDeterminer->isActionAllowed(
      $action,
      $applicationProcessBundle,
      $this->applicationProcessManager->getStatusList($applicationProcessBundle),
    );
  }

}
