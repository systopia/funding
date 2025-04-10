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
use Civi\Funding\FundingCase\Traits\FundingCaseApproveTaskTrait;
use Civi\Funding\Task\Modifier\FundingCaseTaskModifierInterface;

/**
 * Completes an apply funding case task if there's no remaining application
 * process that is in a status in which it can be applied.
 *
 * Should be combined with:
 * @see \Civi\Funding\FundingCase\Task\AbstractFundingCaseApproveTaskCreator
 */
abstract class AbstractFundingCaseApproveTaskModifier implements FundingCaseTaskModifierInterface {

  use FundingCaseApproveTaskTrait;

  /**
   * @throws \CRM_Core_Exception
   */
  public function modifyTask(
    FundingTaskEntity $task,
    FundingCaseBundle $fundingCaseBundle,
    FundingCaseEntity $previousFundingCase
  ): bool {
    if (self::$taskType === $task->getType()) {
      if ($this->isFundingCaseApproved($fundingCaseBundle)) {
        $task->setStatusName(ActivityStatusNames::COMPLETED);

        return TRUE;
      }

      if (FundingCaseStatus::OPEN !== $fundingCaseBundle->getFundingCase()->getStatus() ||
        $this->existsApplicationWithUndecidedEligibility($fundingCaseBundle)
      ) {
        $task->setStatusName(ActivityStatusNames::CANCELLED);

        return TRUE;
      }
    }

    return FALSE;
  }

  private function isFundingCaseApproved(FundingCaseBundle $fundingCaseBundle): bool {
    return NULL !== $fundingCaseBundle->getFundingCase()->getAmountApproved();
  }

}
