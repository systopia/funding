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

namespace Civi\Funding\Task;

use Civi\Api4\FundingTask;
use Civi\Funding\ActivityStatusTypes;
use Civi\Funding\Entity\FundingTaskEntity;
use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\Api4\Query\ConditionInterface;
use Civi\RemoteTools\RequestContext\RequestContextInterface;

final class FundingTaskManager implements FundingTaskManagerInterface {

  private Api4Interface $api4;

  private RequestContextInterface $requestContext;

  public function __construct(Api4Interface $api4, RequestContextInterface $requestContext) {
    $this->api4 = $api4;
    $this->requestContext = $requestContext;
  }

  public function addTask(FundingTaskEntity $task): FundingTaskEntity {
    $existingTask = $this->getOpenTask(
      $task->getActivityTypeName(),
      $task->getSourceRecordId(),
      $task->getType()
    );

    if (NULL !== $existingTask) {
      return $existingTask;
    }

    $now = new \DateTime(date('YmdHis'));
    $task->setCreatedDate($now);
    $task->setModifiedDate($now);
    $values = $this->api4->createEntity(FundingTask::getEntityName(),
      ['source_contact_id' => $this->getSourceContactId()] + $task->toPersistArray()
    )->single();
    // @phpstan-ignore argument.type
    $task->setValues($task->toArray() + $values);

    return $task;
  }

  public function getOpenTask(string $activityTypeName, int $entityId, string $type): ?FundingTaskEntity {
    $task = $this->api4->execute(FundingTask::getEntityName(), 'get', [
      'ignoreCasePermissions' => TRUE,
      'useAssigneeFilter' => FALSE,
      'statusType' => ActivityStatusTypes::INCOMPLETE,
      'where' => [
        ['activity_type_id:name', '=', $activityTypeName],
        ['funding_case_task.type', '=', $type],
        ['source_record_id', '=', $entityId],
      ],
      // Order just in case status where changed manually and there's more than one open task of the given type.
      'orderBy' => ['id' => 'DESC'],
      'limit' => 1,
    ])->first();

    // @phpstan-ignore argument.type
    return NULL === $task ? NULL : FundingTaskEntity::fromArray($task);
  }

  public function getOpenTasks(string $activityTypeName, int $entityId): array {
    $result = $this->api4->execute(FundingTask::getEntityName(), 'get', [
      'ignoreCasePermissions' => TRUE,
      'useAssigneeFilter' => FALSE,
      'statusType' => ActivityStatusTypes::INCOMPLETE,
      'where' => [
        ['activity_type_id:name', '=', $activityTypeName],
        ['source_record_id', '=', $entityId],
      ],
    ]);

    // @phpstan-ignore return.type
    return FundingTaskEntity::allFromApiResult($result);
  }

  public function getOpenTasksBy(string $activityTypeName, ConditionInterface $condition): array {
    $result = $this->api4->execute(FundingTask::getEntityName(), 'get', [
      'ignoreCasePermissions' => TRUE,
      'useAssigneeFilter' => FALSE,
      'statusType' => ActivityStatusTypes::INCOMPLETE,
      'where' => [
        ['activity_type_id:name', '=', $activityTypeName],
        $condition->toArray(),
      ],
    ]);

    // @phpstan-ignore return.type
    return FundingTaskEntity::allFromApiResult($result);
  }

  public function updateTask(FundingTaskEntity $task): void {
    $task->setModifiedDate(new \DateTime(date('YmdHis')));
    $this->api4->updateEntity(
      FundingTask::getEntityName(),
      $task->getId(),
      $task->toPersistArray(),
      ['ignoreCasePermissions' => TRUE]
    );
  }

  private function getSourceContactId(): int {
    return 0 === $this->requestContext->getContactId()
      ? (int) \CRM_Core_BAO_Domain::getDomain()->contact_id
      : $this->requestContext->getContactId();
  }

}
