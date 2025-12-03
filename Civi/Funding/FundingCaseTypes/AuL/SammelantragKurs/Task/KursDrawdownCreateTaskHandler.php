<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
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

namespace Civi\Funding\FundingCaseTypes\AuL\SammelantragKurs\Task;

use Civi\Funding\ActivityStatusNames;
use Civi\Funding\Entity\DrawdownBundle;
use Civi\Funding\Entity\FundingCaseBundle;
use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\Entity\FundingTaskEntity;
use Civi\Funding\Entity\PayoutProcessBundle;
use Civi\Funding\FundingCase\FundingCaseStatus;
use Civi\Funding\FundingCaseTypes\AuL\SammelantragKurs\Traits\KursSupportedFundingCaseTypesTrait;
use Civi\Funding\PayoutProcess\Task\AbstractDrawdownCreateTaskHandler;
use Civi\Funding\Task\Modifier\DrawdownCreateTaskModifierInterface;

/**
 * The create drawdown tasks for Kurse are created on funding case approval.
 * When a drawdown is created, the corresponding task is completed, independent
 * of whether there are still available funds. A new task is created at specific
 * times by a scheduled action.
 *
 * phpcs:disable Generic.Files.LineLength.TooLong
 */
final class KursDrawdownCreateTaskHandler extends AbstractDrawdownCreateTaskHandler implements DrawdownCreateTaskModifierInterface {
// phpcs:enable

  use KursSupportedFundingCaseTypesTrait;

  /**
   * @inheritDoc
   */
  public function createTasksOnChange(
    FundingCaseBundle $fundingCaseBundle,
    FundingCaseEntity $previousFundingCase
  ): iterable {
    $fundingCase = $fundingCaseBundle->getFundingCase();
    if (FundingCaseStatus::ONGOING === $fundingCase->getStatus()
      && FundingCaseStatus::ONGOING !== $previousFundingCase->getStatus()
    ) {
      return parent::createTasksOnChange($fundingCaseBundle, $previousFundingCase);
    }

    return [];
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function createTaskOnSchedule(FundingCaseBundle $fundingCaseBundle): ?FundingTaskEntity {
    $fundingCase = $fundingCaseBundle->getFundingCase();
    if (FundingCaseStatus::ONGOING === $fundingCase->getStatus()
      && $fundingCase->getAmountApproved() > 0
    ) {
      $payoutProcess = $this->payoutProcessManager->getLastByFundingCaseId($fundingCase->getId());
      if (NULL === $payoutProcess) {
        // Should not happen.
        return NULL;
      }

      if ($this->payoutProcessManager->getAmountAvailable($payoutProcess) > 0) {
        $payoutProcessBundle = new PayoutProcessBundle($payoutProcess, $fundingCaseBundle);
        $task = $this->createCreateTask($payoutProcessBundle);
        $task->setValues($task->toArray() +
          ['target_contact_id' => [$payoutProcessBundle->getFundingCase()->getRecipientContactId()]]
        );

        return $task;
      }
    }

    return NULL;
  }

  /**
   * @inheritDoc
   */
  public function modifyTaskOnDrawdownCreate(FundingTaskEntity $task, DrawdownBundle $drawdownBundle): bool {
    if (self::$TASK_TYPE !== $task->getType()) {
      return FALSE;
    }

    $task->setStatusName(ActivityStatusNames::COMPLETED);

    return TRUE;
  }

}
