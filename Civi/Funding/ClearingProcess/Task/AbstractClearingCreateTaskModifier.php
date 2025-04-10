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

use Civi\Funding\ActivityStatusNames;
use Civi\Funding\ClearingProcess\Traits\ClearingCreateTaskTrait;
use Civi\Funding\Entity\ClearingProcessEntity;
use Civi\Funding\Entity\ClearingProcessEntityBundle;
use Civi\Funding\Entity\FundingTaskEntity;
use Civi\Funding\Task\Modifier\ClearingProcessTaskModifierInterface;

/**
 * Should be combined with:
 * @see \Civi\Funding\ClearingProcess\Task\AbstractClearingCreateTaskCreatorOnApplicationChange
 * @see \Civi\Funding\ClearingProcess\Task\AbstractClearingCreateTaskCreatorOnFundingCaseChange
 */
abstract class AbstractClearingCreateTaskModifier implements ClearingProcessTaskModifierInterface {

  use ClearingCreateTaskTrait;

  /**
   * @phpstan-return list<string>
   */
  abstract public static function getSupportedFundingCaseTypes(): array;

  public function modifyTask(
    FundingTaskEntity $task,
    ClearingProcessEntityBundle $clearingProcessBundle,
    ClearingProcessEntity $previousClearingProcess
  ): bool {
    if (self::$taskType !== $task->getType()
      || !$this->isClearingStarted($clearingProcessBundle->getClearingProcess())
    ) {
      return FALSE;
    }

    $task->setStatusName(ActivityStatusNames::COMPLETED);

    return TRUE;
  }

}
