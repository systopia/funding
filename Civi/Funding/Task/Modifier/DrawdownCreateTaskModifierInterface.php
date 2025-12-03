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

namespace Civi\Funding\Task\Modifier;

use Civi\Funding\Entity\DrawdownBundle;
use Civi\Funding\Entity\FundingTaskEntity;

/**
 * Allows to modify tasks when a drawdown is created.
 *
 * @phpstan-import-type taskNameT from \Civi\Funding\ActivityTypeNames
 */
interface DrawdownCreateTaskModifierInterface {

  /**
   * @phpstan-return taskNameT
   */
  public function getActivityTypeName(): string;

  /**
   * Called when a drawdown is created. The task has a reference to the funding
   * case the drawdown belongs to.
   */
  public function modifyTaskOnDrawdownCreate(FundingTaskEntity $task,
    DrawdownBundle $drawdownBundle): bool;

}
