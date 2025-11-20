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

namespace Civi\Funding\Task;

use Civi\Funding\Entity\FundingTaskEntity;
use Civi\RemoteTools\Api4\Query\ConditionInterface;

/**
 * Manages tasks for funding cases, application processes and clearing
 * processes.
 *
 * Each entity can have only one open task per type.
 *
 * @phpstan-import-type taskNameT from \Civi\Funding\ActivityTypeNames
 */
interface FundingTaskManagerInterface {

  /**
   * @throws \CRM_Core_Exception
   */
  public function addTask(FundingTaskEntity $task): FundingTaskEntity;

  /**
   * Tasks are looked up independent of active contact's permissions.
   *
   * @phpstan-param taskNameT $activityTypeName
   *
   * @throws \CRM_Core_Exception
   */
  public function getOpenTask(string $activityTypeName, int $entityId, string $type): ?FundingTaskEntity;

  /**
   * Tasks are looked up independent of active contact's permissions.
   *
   * @phpstan-param taskNameT $activityTypeName
   *
   * @phpstan-return list<FundingTaskEntity>
   *
   * @throws \CRM_Core_Exception
   */
  public function getOpenTasks(string $activityTypeName, int $entityId): array;

  /**
   * Tasks are looked up independent of active contact's permissions.
   *
   * @phpstan-param taskNameT $activityTypeName
   *
   * @phpstan-return list<FundingTaskEntity>
   *
   * @throws \CRM_Core_Exception
   */
  public function getOpenTasksBy(string $activityTypeName, ConditionInterface $condition): array;

  /**
   * Task is updated independent of active contact's permissions.
   *
   * @throws \CRM_Core_Exception
   */
  public function updateTask(FundingTaskEntity $task): void;

}
