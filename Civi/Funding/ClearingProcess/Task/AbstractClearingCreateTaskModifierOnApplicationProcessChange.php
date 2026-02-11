<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as published by
 *  the Free Software Foundation in version 3, or (at your option) any
 *  later version.
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

use Civi\Funding\ActivityTypeNames;
use Civi\Funding\Entity\ApplicationProcessEntity;
use Civi\Funding\Entity\ApplicationProcessEntityBundle;
use Civi\Funding\Entity\FundingTaskEntity;
use Civi\Funding\Task\Modifier\ApplicationProcessTaskModifierInterface;

/**
 * Should be combined with:
 * @see \Civi\Funding\ClearingProcess\Task\AbstractClearingCreateTaskCreatorOnApplicationChange
 * @see \Civi\Funding\ClearingProcess\Task\AbstractClearingCreateTaskCreatorOnFundingCaseChange
 *
 * phpcs:disable Generic.Files.LineLength.TooLong
 */
abstract class AbstractClearingCreateTaskModifierOnApplicationProcessChange implements ApplicationProcessTaskModifierInterface {
// phpcs:enable
  public const TASK_TYPE = 'create';

  /**
   * @phpstan-return list<string>
   */
  abstract public static function getSupportedFundingCaseTypes(): array;

  /**
   * @phpstan-return ActivityTypeNames::CLEARING_PROCESS_TASK
   */
  public function getActivityTypeName(): string {
    return ActivityTypeNames::CLEARING_PROCESS_TASK;
  }

  public function modifyTask(
    FundingTaskEntity $task,
    ApplicationProcessEntityBundle $applicationProcessBundle,
    ApplicationProcessEntity $previousApplicationProcess
  ): bool {
    if (self::TASK_TYPE !== $task->getType()) {
      return FALSE;
    }

    $newDueDate = $this->getDueDate($applicationProcessBundle, $previousApplicationProcess, $task);
    // @phpstan-ignore notEqual.notAllowed
    if ($task->getDueDate() != $newDueDate) {
      $task->setDueDate($newDueDate);

      return TRUE;
    }

    return FALSE;
  }

  abstract protected function getDueDate(
    ApplicationProcessEntityBundle $applicationProcessBundle,
    ApplicationProcessEntity $previousApplicationProcess,
    FundingTaskEntity $task
  ): ?\DateTimeInterface;

}
