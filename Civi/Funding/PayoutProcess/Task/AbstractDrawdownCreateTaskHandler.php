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

namespace Civi\Funding\PayoutProcess\Task;

use Civi\Funding\ActivityStatusNames;
use Civi\Funding\Entity\FundingCaseBundle;
use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\Entity\FundingTaskEntity;
use Civi\Funding\Entity\PayoutProcessBundle;
use Civi\Funding\FundingCase\FundingCaseStatus;
use Civi\Funding\PayoutProcess\PayoutProcessManager;
use Civi\Funding\Task\Handler\AbstractFundingCaseTaskHandler;

/**
 * Should be combined with:
 * @see \Civi\Funding\PayoutProcess\Task\AbstractDrawdownCreateTaskCreator
 */
abstract class AbstractDrawdownCreateTaskHandler extends AbstractFundingCaseTaskHandler {

  use DrawdownCreateTaskTrait;

  /**
   * @phpstan-return list<string>
   */
  abstract public static function getSupportedFundingCaseTypes(): array;

  private PayoutProcessManager $payoutProcessManager;

  public function __construct(PayoutProcessManager $payoutProcessManager) {
    $this->payoutProcessManager = $payoutProcessManager;
  }

  /**
   * @inheritDoc
   */
  public function createTasksOnChange(
    FundingCaseBundle $fundingCaseBundle,
    FundingCaseEntity $previousFundingCase
  ): iterable {
    $fundingCase = $fundingCaseBundle->getFundingCase();
    if (FundingCaseStatus::ONGOING === $fundingCase->getStatus()
      && $fundingCase->getAmountApproved() > $previousFundingCase->getAmountApproved()
    ) {
      $payoutProcess = $this->payoutProcessManager->getLastByFundingCaseId($fundingCase->getId());
      if (NULL === $payoutProcess) {
        // Should not happen.
        return [];
      }

      if ($this->payoutProcessManager->getAmountAvailable($payoutProcess) > 0) {
        $payoutProcessBundle = new PayoutProcessBundle($payoutProcess, $fundingCaseBundle);
        yield $this->createCreateTask($payoutProcessBundle);
      }
    }
  }

  /**
   * @inheritDoc
   */
  public function createTasksOnNew(FundingCaseBundle $fundingCaseBundle): iterable {
    return [];
  }

  public function modifyTask(
    FundingTaskEntity $task,
    FundingCaseBundle $fundingCaseBundle,
    FundingCaseEntity $previousFundingCase
  ): bool {
    if (self::$TASK_TYPE !== $task->getType()) {
      return FALSE;
    }

    $fundingCase = $fundingCaseBundle->getFundingCase();
    if (FundingCaseStatus::ONGOING !== $fundingCase->getStatus()) {
      $task->setStatusName(ActivityStatusNames::COMPLETED);

      return TRUE;
    }

    $payoutProcess = $this->payoutProcessManager->getLastByFundingCaseId($fundingCase->getId());
    // Actually $payoutProcess cannot be NULL.
    if (NULL === $payoutProcess || $this->payoutProcessManager->getAmountAvailable($payoutProcess) <= 0) {
      $task->setStatusName(ActivityStatusNames::COMPLETED);

      return TRUE;
    }

    return FALSE;
  }

}
