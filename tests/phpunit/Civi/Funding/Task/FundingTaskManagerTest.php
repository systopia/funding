<?php
declare(strict_types = 1);

namespace Civi\Funding\Task;

use Civi\Api4\FundingTask;
use Civi\Api4\Generic\Result;
use Civi\Funding\ActivityStatusTypes;
use Civi\Funding\ActivityTypeNames;
use Civi\Funding\EntityFactory\FundingCaseFactory;
use Civi\Funding\EntityFactory\FundingTaskFactory;
use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\Api4\Query\Comparison;
use Civi\RemoteTools\RequestContext\RequestContextInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\PhpUnit\ClockMock;

/**
 * @covers \Civi\Funding\Task\FundingTaskManager
 */
final class FundingTaskManagerTest extends TestCase {

  /**
   * @var \Civi\RemoteTools\Api4\Api4Interface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $api4Mock;

  /**
   * @var \Civi\RemoteTools\RequestContext\RequestContextInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $requestContextMock;

  private FundingTaskManager $taskManager;

  public static function setUpBeforeClass(): void {
    parent::setUpBeforeClass();
    ClockMock::register(__CLASS__);
    ClockMock::withClockMock(123456);
  }

  protected function setUp(): void {
    parent::setUp();
    $this->api4Mock = $this->createMock(Api4Interface::class);
    $this->requestContextMock = $this->createMock(RequestContextInterface::class);
    $this->taskManager = new FundingTaskManager(
      $this->api4Mock,
      $this->requestContextMock
    );
  }

  public function testAddTaskWithoutOpenTask(): void {
    $task = FundingTaskFactory::create([
      'activity_type_id:name' => ActivityTypeNames::FUNDING_CASE_TASK,
      'type' => 'test',
      'required_permissions' => ['some_permission'],
    ]);

    $this->api4Mock->expects(static::once())->method('execute')
      ->with(FundingTask::getEntityName(), 'get', [
        'ignoreCasePermissions' => TRUE,
        'useAssigneeFilter' => FALSE,
        'statusType' => ActivityStatusTypes::INCOMPLETE,
        'where' => [
          ['activity_type_id:name', '=', ActivityTypeNames::FUNDING_CASE_TASK],
          ['funding_case_task.type', '=', 'test'],
          ['source_record_id', '=', FundingCaseFactory::DEFAULT_ID],
        ],
        'orderBy' => ['id' => 'DESC'],
        'limit' => 1,
      ])->willReturn(new Result());

    $this->requestContextMock->method('getContactId')->willReturn(12);

    $this->api4Mock->expects(static::once())->method('createEntity')
      ->with(FundingTask::getEntityName(),
        [
          'source_contact_id' => 12,
          'created_date' => date('Y-m-d H:i:s'),
          'modified_date' => date('Y-m-d H:i:s'),
        ] + $task->toPersistArray()
      )->willReturn(new Result([
        [
          'created_date' => date('YmdHis'),
          'modified_date' => date('YmdHis'),
        ] + $task->toPersistArray(),
      ]));

    $result = $this->taskManager->addTask($task);
    static::assertSame($task, $result);
    static::assertEquals(new \DateTime(date('YmdHis')), $task->getCreatedDate());
    static::assertEquals(new \DateTime(date('YmdHis')), $task->getModifiedDate());
  }

  public function testAddTaskWithOpenTask(): void {
    $existingTask = FundingTaskFactory::create([
      'subject' => 'Existing Task',
      'activity_type_id:name' => ActivityTypeNames::FUNDING_CASE_TASK,
      'type' => 'test',
      'required_permissions' => ['some_permission'],
    ]);
    $newTask = FundingTaskFactory::create([
      'subject' => 'New Task',
      'activity_type_id:name' => ActivityTypeNames::FUNDING_CASE_TASK,
      'type' => 'test',
    ]);

    $this->api4Mock->expects(static::once())->method('execute')
      ->with(FundingTask::getEntityName(), 'get', [
        'ignoreCasePermissions' => TRUE,
        'useAssigneeFilter' => FALSE,
        'statusType' => ActivityStatusTypes::INCOMPLETE,
        'where' => [
          ['activity_type_id:name', '=', ActivityTypeNames::FUNDING_CASE_TASK],
          ['funding_case_task.type', '=', 'test'],
          ['source_record_id', '=', FundingCaseFactory::DEFAULT_ID],
        ],
        'orderBy' => ['id' => 'DESC'],
        'limit' => 1,
      ])->willReturn(new Result([$existingTask->toPersistArray()]));

    $this->api4Mock->expects(static::never())->method('createEntity');

    $result = $this->taskManager->addTask($newTask);
    static::assertNotSame($newTask, $result);
  }

  public function testGetOpenTask(): void {
    $existingTask = FundingTaskFactory::create([
      'subject' => 'Existing Task',
      'activity_type_id:name' => ActivityTypeNames::FUNDING_CASE_TASK,
      'type' => 'test',
      'required_permissions' => ['some_permission'],
    ]);

    $this->api4Mock->expects(static::once())->method('execute')
      ->with(FundingTask::getEntityName(), 'get', [
        'ignoreCasePermissions' => TRUE,
        'useAssigneeFilter' => FALSE,
        'statusType' => ActivityStatusTypes::INCOMPLETE,
        'where' => [
          ['activity_type_id:name', '=', ActivityTypeNames::APPLICATION_PROCESS_TASK],
          ['funding_case_task.type', '=', 'test'],
          ['source_record_id', '=', 123],
        ],
        'orderBy' => ['id' => 'DESC'],
        'limit' => 1,
      ])->willReturn(new Result([$existingTask->toPersistArray()]));

    $task = $this->taskManager->getOpenTask(ActivityTypeNames::APPLICATION_PROCESS_TASK, 123, 'test');
    static::assertEquals($existingTask, $task);
  }

  public function testGetOpenTasks(): void {
    $existingTask = FundingTaskFactory::create([
      'subject' => 'Existing Task',
      'activity_type_id:name' => ActivityTypeNames::FUNDING_CASE_TASK,
      'type' => 'test',
      'required_permissions' => ['some_permission'],
    ]);

    $this->api4Mock->expects(static::once())->method('execute')
      ->with(FundingTask::getEntityName(), 'get', [
        'ignoreCasePermissions' => TRUE,
        'useAssigneeFilter' => FALSE,
        'statusType' => ActivityStatusTypes::INCOMPLETE,
        'where' => [
          ['activity_type_id:name', '=', ActivityTypeNames::FUNDING_CASE_TASK],
          ['source_record_id', '=', 123],
        ],
      ])->willReturn(new Result([$existingTask->toPersistArray()]));

    $tasks = $this->taskManager->getOpenTasks(ActivityTypeNames::FUNDING_CASE_TASK, 123);
    static::assertEquals([$existingTask], $tasks);
  }

  public function testGetOpenTasksBy(): void {
    $existingTask = FundingTaskFactory::create([
      'subject' => 'Existing Task',
      'activity_type_id:name' => ActivityTypeNames::FUNDING_CASE_TASK,
      'type' => 'test',
      'required_permissions' => ['some_permission'],
    ]);

    $this->api4Mock->expects(static::once())->method('execute')
      ->with(FundingTask::getEntityName(), 'get', [
        'ignoreCasePermissions' => TRUE,
        'useAssigneeFilter' => FALSE,
        'statusType' => ActivityStatusTypes::INCOMPLETE,
        'where' => [
          ['activity_type_id:name', '=', ActivityTypeNames::DRAWDOWN_TASK],
          ['foo', '=', 'bar'],
        ],
      ])->willReturn(new Result([$existingTask->toPersistArray()]));

    $tasks = $this->taskManager->getOpenTasksBy(
      ActivityTypeNames::DRAWDOWN_TASK,
      Comparison::new('foo', '=', 'bar')
    );
    static::assertEquals([$existingTask], $tasks);
  }

  public function testUpdate(): void {
    $task = FundingTaskFactory::create([
      'required_permissions' => ['some_permission'],
    ]);
    $task->setValues(['id' => 123] + $task->toArray());

    $this->api4Mock->expects(static::once())->method('updateEntity')
      ->with(FundingTask::getEntityName(), 123, [
        'modified_date' => date('Y-m-d H:i:s'),
      ] + $task->toPersistArray())
      ->willReturn(new Result([$task->toPersistArray()]));

    $this->taskManager->updateTask($task);
    static::assertEquals(new \DateTime(date('YmdHis')), $task->getModifiedDate());
  }

}
