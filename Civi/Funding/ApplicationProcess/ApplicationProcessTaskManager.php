<?php
/*
 * Copyright (C) 2023 SYSTOPIA GmbH
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

namespace Civi\Funding\ApplicationProcess;

use Civi\Funding\ActivityTypeIds;
use Civi\Funding\Entity\ActivityEntity;
use Civi\Funding\Entity\ApplicationProcessEntity;
use Civi\RemoteTools\Api4\Query\Comparison;
use Civi\RemoteTools\Api4\Query\CompositeCondition;
use CRM_Funding_ExtensionUtil as E;

/**
 * Manages tasks for funding applications.
 *
 * Each application can have only one open task per type.
 */
class ApplicationProcessTaskManager {

  private ApplicationProcessActivityManager $activityManager;

  public function __construct(ApplicationProcessActivityManager $activityManager) {
    $this->activityManager = $activityManager;
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function addExternalTask(
    int $contactId,
    ApplicationProcessEntity $applicationProcess,
    string $type,
    string $subject
  ): ActivityEntity {
    $task = $this->getOpenExternalTask($applicationProcess->getId(), $type);
    if (NULL === $task) {
      $task = ActivityEntity::fromArray([
        'activity_type_id' => ActivityTypeIds::FUNDING_APPLICATION_TASK_EXTERNAL,
        'status_id:name' => 'Available',
        'subject' => $subject,
        'details' => E::ts(
          'Application process: %1 (%2)', [
            1 => $applicationProcess->getTitle(),
            2 => $applicationProcess->getIdentifier(),
          ]
        ),
        'funding_application_task.type' => $type,
      ]);
      $this->activityManager->addActivity($contactId, $applicationProcess, $task);
    }

    return $task;
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function addOrAssignInternalTask(
    int $contactId,
    ApplicationProcessEntity $applicationProcess,
    ?int $assigneeContactId,
    string $type,
    string $subject
  ): ActivityEntity {
    $task = $this->getOpenInternalTask($applicationProcess->getId(), $type);
    if (NULL === $task) {
      $task = ActivityEntity::fromArray([
        'activity_type_id' => ActivityTypeIds::FUNDING_APPLICATION_TASK_INTERNAL,
        'status_id:name' => 'Available',
        'subject' => $subject,
        'details' => E::ts(
          'Application process: %1 (%2)', [
            1 => $applicationProcess->getTitle(),
            2 => $applicationProcess->getIdentifier(),
          ]
        ),
        'assignee_contact_id' => $assigneeContactId,
        'funding_application_task.type' => $type,
      ]);
      $this->activityManager->addActivity($contactId, $applicationProcess, $task);
    }
    else {
      $this->activityManager->assignActivity($task, $assigneeContactId);
    }

    return $task;
  }

  /**
   * Assigns an open task of the given type to the given contact ID. Nothing
   * happens, if no such task exists.
   *
   * @param int|null $assigneeContactId NULL to unassign.
   *
   * @throws \CRM_Core_Exception
   */
  public function assignInternalTask(
    int $applicationProcessId,
    ?int $assigneeContactId,
    string $type
  ): void {
    $task = $this->getOpenInternalTask($applicationProcessId, $type);
    if (NULL !== $task) {
      $this->activityManager->assignActivity($task, $assigneeContactId);
    }
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function cancelExternalTask(int $applicationProcessId, string $type): void {
    $task = $this->getOpenExternalTask($applicationProcessId, $type);
    if (NULL !== $task) {
      $this->activityManager->cancelActivity($task);
    }
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function cancelInternalTask(int $applicationProcessId, string $type): void {
    $task = $this->getOpenInternalTask($applicationProcessId, $type);
    if (NULL !== $task) {
      $this->activityManager->cancelActivity($task);
    }
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function completeExternalTask(int $applicationProcessId, string $type): void {
    $task = $this->getOpenExternalTask($applicationProcessId, $type);
    if (NULL !== $task) {
      $this->activityManager->completeActivity($task);
    }
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function completeInternalTask(int $applicationProcessId, string $type): void {
    $task = $this->getOpenInternalTask($applicationProcessId, $type);
    if (NULL !== $task) {
      $this->activityManager->completeActivity($task);
    }
  }

  /**
   * @phpstan-return array<ActivityEntity>
   *
   * @throws \CRM_Core_Exception
   */
  public function getExternalTasks(int $applicationProcessId): array {
    return $this->activityManager->getByApplicationProcess(
      $applicationProcessId,
      Comparison::new('activity_type_id', '=', ActivityTypeIds::FUNDING_APPLICATION_TASK_EXTERNAL),
    );
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function getOpenExternalTask(int $applicationProcessId, string $type): ?ActivityEntity {
    $tasks = $this->activityManager->getOpenByApplicationProcess(
      $applicationProcessId,
      CompositeCondition::fromFieldValuePairs([
        'activity_type_id' => ActivityTypeIds::FUNDING_APPLICATION_TASK_EXTERNAL,
        'funding_application_task.type' => $type,
      ]),
    );

    return $tasks[0] ?? NULL;
  }

  /**
   * @phpstan-return array<ActivityEntity>
   *
   * @throws \CRM_Core_Exception
   */
  public function getOpenExternalTasks(int $applicationProcessId): array {
    return $this->activityManager->getOpenByApplicationProcess(
      $applicationProcessId,
      Comparison::new('activity_type_id', '=', ActivityTypeIds::FUNDING_APPLICATION_TASK_EXTERNAL),
    );
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function getOpenInternalTask(int $applicationProcessId, string $type): ?ActivityEntity {
    $tasks = $this->activityManager->getOpenByApplicationProcess(
      $applicationProcessId,
      CompositeCondition::fromFieldValuePairs([
        'activity_type_id' => ActivityTypeIds::FUNDING_APPLICATION_TASK_INTERNAL,
        'funding_application_task.type' => $type,
      ]),
    );

    return $tasks[0] ?? NULL;
  }

}
