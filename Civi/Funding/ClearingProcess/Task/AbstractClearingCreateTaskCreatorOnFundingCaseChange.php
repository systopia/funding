<?php
/*
 * Copyright (C) 2024 SYSTOPIA GmbH
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

namespace Civi\Funding\ClearingProcess\Task;

use Civi\Funding\ApplicationProcess\ApplicationProcessManager;
use Civi\Funding\ClearingProcess\ClearingProcessManager;
use Civi\Funding\ClearingProcess\Traits\ClearingCreateTaskTrait;
use Civi\Funding\Entity\ApplicationProcessEntityBundle;
use Civi\Funding\Entity\ClearingProcessEntityBundle;
use Civi\Funding\Entity\FundingCaseBundle;
use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\Task\Creator\FundingCaseTaskCreatorInterface;

/**
 * Should be combined with:
 * @see \Civi\Funding\ClearingProcess\Task\AbstractClearingCreateTaskCreatorOnApplicationChange
 * @see \Civi\Funding\ClearingProcess\Task\AbstractClearingCreateTaskModifier
 *
 * Might be combined with:
 * @see \Civi\Funding\ApplicationProcess\Task\AbstractClearingCreateTaskModifierOnApplicationProcessChange
 */
abstract class AbstractClearingCreateTaskCreatorOnFundingCaseChange implements FundingCaseTaskCreatorInterface {

  use ClearingCreateTaskTrait;

  private ApplicationProcessManager $applicationProcessManager;

  private ClearingProcessManager $clearingProcessManager;

  /**
   * @phpstan-return list<string>
   */
  abstract public static function getSupportedFundingCaseTypes(): array;

  public function __construct(
    ApplicationProcessManager $applicationProcessManager,
    ClearingProcessManager $clearingProcessManager
  ) {
    $this->applicationProcessManager = $applicationProcessManager;
    $this->clearingProcessManager = $clearingProcessManager;
  }

  public function createTasksOnChange(
    FundingCaseBundle $fundingCaseBundle,
    FundingCaseEntity $previousFundingCase
  ): iterable {
    if ($this->isChangedToApproved($fundingCaseBundle, $previousFundingCase)) {
      $applicationProcesses = $this->applicationProcessManager->getByFundingCaseId(
        $fundingCaseBundle->getFundingCase()->getId()
      );
      foreach ($applicationProcesses as $applicationProcess) {
        if (TRUE !== $applicationProcess->getIsEligible()) {
          continue;
        }

        $clearingProcess = $this->clearingProcessManager->getByApplicationProcessId($applicationProcess->getId());
        if (NULL !== $clearingProcess && !$this->isClearingStarted($clearingProcess)) {
          yield $this->createCreateTask(new ClearingProcessEntityBundle($clearingProcess,
            new ApplicationProcessEntityBundle(
              $applicationProcess,
              $fundingCaseBundle->getFundingCase(),
              $fundingCaseBundle->getFundingCaseType(),
              $fundingCaseBundle->getFundingProgram()
            )
          ));
        }
      }
    }
  }

  /**
   * @codeCoverageIgnore
   */
  public function createTasksOnNew(FundingCaseBundle $fundingCaseBundle): iterable {
    return [];
  }

  private function isChangedToApproved(
    FundingCaseBundle $fundingCaseBundle,
    FundingCaseEntity $previousFundingCase
  ): bool {
    return NULL !== $fundingCaseBundle->getFundingCase()->getAmountApproved()
      && NULL === $previousFundingCase->getAmountApproved();
  }

}
