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

use Civi\Funding\ClearingProcess\ClearingProcessManager;
use Civi\Funding\ClearingProcess\Traits\ClearingCreateTaskTrait;
use Civi\Funding\Entity\ApplicationProcessEntity;
use Civi\Funding\Entity\ApplicationProcessEntityBundle;
use Civi\Funding\Entity\ClearingProcessEntityBundle;
use Civi\Funding\Task\Creator\ApplicationProcessTaskCreatorInterface;

/**
 * Should be combined with:
 * @see \Civi\Funding\ClearingProcess\Task\AbstractClearingCreateTaskCreatorOnFundingCaseChange
 * @see \Civi\Funding\ClearingProcess\Task\AbstractClearingCreateTaskModifier
 *
 * Might be combined with:
 * @see \Civi\Funding\ApplicationProcess\Task\AbstractClearingCreateTaskModifierOnApplicationProcessChange
 */
abstract class AbstractClearingCreateTaskCreatorOnApplicationChange implements ApplicationProcessTaskCreatorInterface {

  use ClearingCreateTaskTrait;

  private ClearingProcessManager $clearingProcessManager;

  /**
   * @phpstan-return list<string>
   */
  abstract public static function getSupportedFundingCaseTypes(): array;

  public function __construct(ClearingProcessManager $clearingProcessManager) {
    $this->clearingProcessManager = $clearingProcessManager;
  }

  public function createTasksOnChange(
    ApplicationProcessEntityBundle $applicationProcessBundle,
    ApplicationProcessEntity $previousApplicationProcess
  ): iterable {
    if ($this->isChangedToEligible($applicationProcessBundle, $previousApplicationProcess)
      && $this->isFundingCaseApproved($applicationProcessBundle)
    ) {
      $clearingProcess = $this->clearingProcessManager->getByApplicationProcessId(
        $applicationProcessBundle->getApplicationProcess()->getId()
      );
      if (NULL !== $clearingProcess && !$this->isClearingStarted($clearingProcess)) {
        yield $this->createCreateTask(new ClearingProcessEntityBundle($clearingProcess, $applicationProcessBundle));
      }
    }
  }

  /**
   * @codeCoverageIgnore
   */
  public function createTasksOnNew(ApplicationProcessEntityBundle $applicationProcessBundle): iterable {
    return [];
  }

  private function isChangedToEligible(
    ApplicationProcessEntityBundle $applicationProcessBundle,
    ApplicationProcessEntity $previousApplicationProcess
  ): bool {
    return $applicationProcessBundle->getApplicationProcess()->getIsEligible()
      !== $previousApplicationProcess->getIsEligible()
      && TRUE === $applicationProcessBundle->getApplicationProcess()->getIsEligible();
  }

  private function isFundingCaseApproved(ApplicationProcessEntityBundle $applicationProcessBundle): bool {
    return $applicationProcessBundle->getFundingCase()->getAmountApproved() !== NULL;
  }

}
