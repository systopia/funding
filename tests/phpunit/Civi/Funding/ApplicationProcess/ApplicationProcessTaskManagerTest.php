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
use Civi\Funding\EntityFactory\ApplicationProcessFactory;
use Civi\RemoteTools\Api4\Query\Comparison;
use Civi\RemoteTools\Api4\Query\CompositeCondition;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\ApplicationProcess\ApplicationProcessTaskManager
 */
final class ApplicationProcessTaskManagerTest extends TestCase {

  /**
   * @var \Civi\Funding\ApplicationProcess\ApplicationProcessActivityManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $activityManagerMock;

  private ApplicationProcessTaskManager $taskManager;

  protected function setUp(): void {
    parent::setUp();
    $this->activityManagerMock = $this->createMock(ApplicationProcessActivityManager::class);
    $this->taskManager = new ApplicationProcessTaskManager($this->activityManagerMock);
  }

  public function testAddExternalTask(): void {
    $applicationProcess = ApplicationProcessFactory::createApplicationProcess([
      'title' => 'Title',
      'identifier' => 'Identifier',
    ]);
    $contactId = 12;

    $this->activityManagerMock->expects(static::once())->method('getOpenByApplicationProcess')
      ->with($applicationProcess->getId(), CompositeCondition::fromFieldValuePairs([
        'activity_type_id' => ActivityTypeIds::FUNDING_APPLICATION_TASK_EXTERNAL,
        'funding_application_task.type' => 'test-type',
      ]))->willReturn([]);
    $this->activityManagerMock->expects(static::once())->method('addActivity')
      ->with($contactId, $applicationProcess, static::isInstanceOf(ActivityEntity::class));

    $task = $this->taskManager->addExternalTask($contactId, $applicationProcess, 'test-type', 'Subject');
    static::assertSame(ActivityTypeIds::FUNDING_APPLICATION_TASK_EXTERNAL, $task->getActivityTypeId());
    static::assertSame('test-type', $task->get('funding_application_task.type'));
    static::assertSame('Subject', $task->getSubject());
    static::assertSame('Application process: Title (Identifier)', $task->getDetails());
    static::assertSame('Available', $task->get('status_id:name'));
  }

  public function testAddOrAssignInternalTaskNew(): void {
    $applicationProcess = ApplicationProcessFactory::createApplicationProcess([
      'title' => 'Title',
      'identifier' => 'Identifier',
    ]);
    $contactId = 12;
    $assigneeContactId = 34;

    $this->activityManagerMock->expects(static::once())->method('getOpenByApplicationProcess')
      ->with($applicationProcess->getId(), CompositeCondition::fromFieldValuePairs([
        'activity_type_id' => ActivityTypeIds::FUNDING_APPLICATION_TASK_INTERNAL,
        'funding_application_task.type' => 'test-type',
      ]))->willReturn([]);
    $this->activityManagerMock->expects(static::once())->method('addActivity')
      ->with($contactId, $applicationProcess, static::isInstanceOf(ActivityEntity::class));

    $task = $this->taskManager->addOrAssignInternalTask(
      $contactId,
      $applicationProcess,
      $assigneeContactId,
      'test-type',
      'Subject'
    );
    static::assertSame(ActivityTypeIds::FUNDING_APPLICATION_TASK_INTERNAL, $task->getActivityTypeId());
    static::assertSame('test-type', $task->get('funding_application_task.type'));
    static::assertSame('Subject', $task->getSubject());
    static::assertSame('Application process: Title (Identifier)', $task->getDetails());
    static::assertSame('Available', $task->get('status_id:name'));
    static::assertSame($assigneeContactId, $task->get('assignee_contact_id'));
  }

  public function testAddOrAssignInternalTaskExisting(): void {
    $applicationProcess = ApplicationProcessFactory::createApplicationProcess();
    $contactId = 12;
    $assigneeContactId = 34;

    $task = ActivityEntity::fromArray([
      'activity_type_id' => ActivityTypeIds::FUNDING_APPLICATION_TASK_INTERNAL,
      'subject' => 'Subject',
    ]);

    $this->activityManagerMock->expects(static::once())->method('getOpenByApplicationProcess')
      ->with($applicationProcess->getId(), CompositeCondition::fromFieldValuePairs([
        'activity_type_id' => ActivityTypeIds::FUNDING_APPLICATION_TASK_INTERNAL,
        'funding_application_task.type' => 'test-type',
      ]))->willReturn([$task]);
    $this->activityManagerMock->expects(static::once())->method('assignActivity')
      ->with($task, $assigneeContactId);

    static::assertSame($task, $this->taskManager->addOrAssignInternalTask(
      $contactId,
      $applicationProcess,
      $assigneeContactId,
      'test-type',
      'Subject'
    ));
  }

  public function testAssignInternalTask(): void {
    $applicationProcessId = 12;
    $assigneeContactId = 34;

    $task = ActivityEntity::fromArray([
      'activity_type_id' => ActivityTypeIds::FUNDING_APPLICATION_TASK_INTERNAL,
      'subject' => 'Subject',
    ]);

    $this->activityManagerMock->expects(static::once())->method('getOpenByApplicationProcess')
      ->with($applicationProcessId, CompositeCondition::fromFieldValuePairs([
        'activity_type_id' => ActivityTypeIds::FUNDING_APPLICATION_TASK_INTERNAL,
        'funding_application_task.type' => 'test-type',
      ]))->willReturn([$task]);

    $this->activityManagerMock->expects(static::once())->method('assignActivity')
      ->with($task, $assigneeContactId);

    $this->taskManager->assignInternalTask($applicationProcessId, $assigneeContactId, 'test-type');
  }

  public function testCancelExternalTask(): void {
    $applicationProcessId = 12;
    $task = ActivityEntity::fromArray([
      'activity_type_id' => ActivityTypeIds::FUNDING_APPLICATION_TASK_INTERNAL,
      'subject' => 'Subject',
    ]);

    $this->activityManagerMock->expects(static::once())->method('getOpenByApplicationProcess')
      ->with($applicationProcessId, CompositeCondition::fromFieldValuePairs([
        'activity_type_id' => ActivityTypeIds::FUNDING_APPLICATION_TASK_EXTERNAL,
        'funding_application_task.type' => 'test-type',
      ]))->willReturn([$task]);

    $this->activityManagerMock->expects(static::once())->method('cancelActivity')
      ->with($task);

    $this->taskManager->cancelExternalTask($applicationProcessId, 'test-type');
  }

  public function testCancelInternalTask(): void {
    $applicationProcessId = 12;
    $task = ActivityEntity::fromArray([
      'activity_type_id' => ActivityTypeIds::FUNDING_APPLICATION_TASK_INTERNAL,
      'subject' => 'Subject',
    ]);

    $this->activityManagerMock->expects(static::once())->method('getOpenByApplicationProcess')
      ->with($applicationProcessId, CompositeCondition::fromFieldValuePairs([
        'activity_type_id' => ActivityTypeIds::FUNDING_APPLICATION_TASK_INTERNAL,
        'funding_application_task.type' => 'test-type',
      ]))->willReturn([$task]);

    $this->activityManagerMock->expects(static::once())->method('cancelActivity')
      ->with($task);

    $this->taskManager->cancelInternalTask($applicationProcessId, 'test-type');
  }

  public function testCompleteExternalTask(): void {
    $applicationProcessId = 12;
    $task = ActivityEntity::fromArray([
      'activity_type_id' => ActivityTypeIds::FUNDING_APPLICATION_TASK_INTERNAL,
      'subject' => 'Subject',
    ]);

    $this->activityManagerMock->expects(static::once())->method('getOpenByApplicationProcess')
      ->with($applicationProcessId, CompositeCondition::fromFieldValuePairs([
        'activity_type_id' => ActivityTypeIds::FUNDING_APPLICATION_TASK_EXTERNAL,
        'funding_application_task.type' => 'test-type',
      ]))->willReturn([$task]);

    $this->activityManagerMock->expects(static::once())->method('completeActivity')
      ->with($task);

    $this->taskManager->completeExternalTask($applicationProcessId, 'test-type');
  }

  public function testCompleteInternalTask(): void {
    $applicationProcessId = 12;
    $task = ActivityEntity::fromArray([
      'activity_type_id' => ActivityTypeIds::FUNDING_APPLICATION_TASK_INTERNAL,
      'subject' => 'Subject',
    ]);

    $this->activityManagerMock->expects(static::once())->method('getOpenByApplicationProcess')
      ->with($applicationProcessId, CompositeCondition::fromFieldValuePairs([
        'activity_type_id' => ActivityTypeIds::FUNDING_APPLICATION_TASK_INTERNAL,
        'funding_application_task.type' => 'test-type',
      ]))->willReturn([$task]);

    $this->activityManagerMock->expects(static::once())->method('completeActivity')
      ->with($task);

    $this->taskManager->completeInternalTask($applicationProcessId, 'test-type');
  }

  public function testGetExternalTasks(): void {
    $applicationProcessId = 12;
    $task = ActivityEntity::fromArray([
      'activity_type_id' => ActivityTypeIds::FUNDING_APPLICATION_TASK_INTERNAL,
      'subject' => 'Subject',
    ]);

    $this->activityManagerMock->expects(static::once())->method('getByApplicationProcess')
      ->with(
        $applicationProcessId,
        Comparison::new('activity_type_id', '=', ActivityTypeIds::FUNDING_APPLICATION_TASK_EXTERNAL)
      )->willReturn([$task]);

    static::assertSame([$task], $this->taskManager->getExternalTasks($applicationProcessId));
  }

  public function testGetOpenExternalTask(): void {
    $applicationProcessId = 12;
    $task = ActivityEntity::fromArray([
      'activity_type_id' => ActivityTypeIds::FUNDING_APPLICATION_TASK_EXTERNAL,
      'subject' => 'Subject',
    ]);

    $this->activityManagerMock->expects(static::once())->method('getOpenByApplicationProcess')
      ->with($applicationProcessId, CompositeCondition::fromFieldValuePairs([
        'activity_type_id' => ActivityTypeIds::FUNDING_APPLICATION_TASK_EXTERNAL,
        'funding_application_task.type' => 'test-type',
      ]))->willReturn([$task]);

    static::assertSame($task, $this->taskManager->getOpenExternalTask($applicationProcessId, 'test-type'));
  }

  public function testGetOpenExternalTaskNone(): void {
    $applicationProcessId = 12;

    $this->activityManagerMock->expects(static::once())->method('getOpenByApplicationProcess')
      ->with($applicationProcessId, CompositeCondition::fromFieldValuePairs([
        'activity_type_id' => ActivityTypeIds::FUNDING_APPLICATION_TASK_EXTERNAL,
        'funding_application_task.type' => 'test-type',
      ]))->willReturn([]);

    static::assertNull($this->taskManager->getOpenExternalTask($applicationProcessId, 'test-type'));
  }

  public function testGetOpenExternalTasks(): void {
    $applicationProcessId = 12;
    $task = ActivityEntity::fromArray([
      'activity_type_id' => ActivityTypeIds::FUNDING_APPLICATION_TASK_EXTERNAL,
      'subject' => 'Subject',
    ]);

    $this->activityManagerMock->expects(static::once())->method('getOpenByApplicationProcess')
      ->with(
        $applicationProcessId,
        Comparison::new('activity_type_id', '=', ActivityTypeIds::FUNDING_APPLICATION_TASK_EXTERNAL)
      )->willReturn([$task]);

    static::assertSame([$task], $this->taskManager->getOpenExternalTasks($applicationProcessId));
  }

  public function testGetOpenInternalTask(): void {
    $applicationProcessId = 12;
    $task = ActivityEntity::fromArray([
      'activity_type_id' => ActivityTypeIds::FUNDING_APPLICATION_TASK_INTERNAL,
      'subject' => 'Subject',
    ]);

    $this->activityManagerMock->expects(static::once())->method('getOpenByApplicationProcess')
      ->with($applicationProcessId, CompositeCondition::fromFieldValuePairs([
        'activity_type_id' => ActivityTypeIds::FUNDING_APPLICATION_TASK_INTERNAL,
        'funding_application_task.type' => 'test-type',
      ]))->willReturn([$task]);

    static::assertSame($task, $this->taskManager->getOpenInternalTask($applicationProcessId, 'test-type'));
  }

  public function testGetOpenInternalTaskNone(): void {
    $applicationProcessId = 12;

    $this->activityManagerMock->expects(static::once())->method('getOpenByApplicationProcess')
      ->with($applicationProcessId, CompositeCondition::fromFieldValuePairs([
        'activity_type_id' => ActivityTypeIds::FUNDING_APPLICATION_TASK_INTERNAL,
        'funding_application_task.type' => 'test-type',
      ]))->willReturn([]);

    static::assertNull($this->taskManager->getOpenInternalTask($applicationProcessId, 'test-type'));
  }

}
