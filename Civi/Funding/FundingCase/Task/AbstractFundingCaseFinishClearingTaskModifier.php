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

namespace Civi\Funding\FundingCase\Task;

use Civi\Funding\ActivityStatusNames;
use Civi\Funding\Entity\FundingCaseBundle;
use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\Entity\FundingTaskEntity;
use Civi\Funding\FundingCase\FundingCaseStatus;
use Civi\Funding\FundingCase\Traits\FundingCaseFinishClearingTaskTrait;
use Civi\Funding\Task\Modifier\FundingCaseTaskModifierInterface;

/**
 * Completes a finish clearing funding case task.
 *
 * Should be combined with:
 * @see \Civi\Funding\FundingCase\Task\AbstractFundingCaseFinishClearingTaskCreator
 */
abstract class AbstractFundingCaseFinishClearingTaskModifier implements FundingCaseTaskModifierInterface {

  use FundingCaseFinishClearingTaskTrait;

  /**
   * @throws \CRM_Core_Exception
   */
  public function modifyTask(
    FundingTaskEntity $task,
    FundingCaseBundle $fundingCaseBundle,
    FundingCaseEntity $previousFundingCase
  ): bool {
    if (self::$taskType === $task->getType()) {
      if ($this->isFundingCaseCleared($fundingCaseBundle)) {
        $task->setStatusName(ActivityStatusNames::COMPLETED);

        return TRUE;
      }

      if (!$this->isFinishClearingPossible($fundingCaseBundle)) {
        $task->setStatusName(ActivityStatusNames::CANCELLED);

        return TRUE;
      }
    }

    return FALSE;
  }

  protected function isFundingCaseCleared(FundingCaseBundle $fundingCaseBundle): bool {
    return FundingCaseStatus::CLEARED === $fundingCaseBundle->getFundingCase()->getStatus();
  }

}
