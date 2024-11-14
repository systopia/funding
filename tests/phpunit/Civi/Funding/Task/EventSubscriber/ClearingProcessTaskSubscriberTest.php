<?php
declare(strict_types = 1);

namespace Civi\Funding\Task\EventSubscriber;

use Civi\Funding\ActivityTypeNames;
use Civi\Funding\EntityFactory\ClearingProcessBundleFactory;
use Civi\Funding\EntityFactory\ClearingProcessFactory;
use Civi\Funding\EntityFactory\FundingCaseTypeFactory;
use Civi\Funding\EntityFactory\FundingTaskFactory;
use Civi\Funding\Event\ClearingProcess\ClearingProcessCreatedEvent;
use Civi\Funding\Event\ClearingProcess\ClearingProcessUpdatedEvent;
use Civi\Funding\Task\Creator\ClearingProcessTaskCreatorInterface;
use Civi\Funding\Task\FundingTaskManager;
use Civi\Funding\Task\Modifier\ClearingProcessTaskModifierInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\Task\EventSubscriber\ClearingProcessTaskSubscriber
 */
final class ClearingProcessTaskSubscriberTest extends TestCase {

  /**
   * @var \Civi\Funding\Task\EventSubscriber\ClearingProcessTaskSubscriber
   */
  private ClearingProcessTaskSubscriber $subscriber;

  /**
   * @var \Civi\Funding\Task\Creator\ClearingProcessTaskCreatorInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $taskCreatorMock;

  /**
   * @var \Civi\Funding\Task\FundingTaskManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $taskManagerMock;

  /**
   * @var \Civi\Funding\Task\Modifier\ClearingProcessTaskModifierInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $taskModifierMock;

  protected function setUp(): void {
    parent::setUp();
    $this->taskManagerMock = $this->createMock(FundingTaskManager::class);
    $this->taskCreatorMock = $this->createMock(ClearingProcessTaskCreatorInterface::class);
    $this->taskModifierMock = $this->createMock(ClearingProcessTaskModifierInterface::class);
    $this->subscriber = new ClearingProcessTaskSubscriber(
      $this->taskManagerMock,
      [FundingCaseTypeFactory::DEFAULT_NAME => [$this->taskCreatorMock]],
      [FundingCaseTypeFactory::DEFAULT_NAME => [$this->taskModifierMock]]
    );
  }

  public function testGetSubscribedEvents(): void {
    $expectedSubscriptions = [
      ClearingProcessCreatedEvent::class => 'onCreated',
      ClearingProcessUpdatedEvent::class => 'onUpdated',
    ];

    static::assertEquals($expectedSubscriptions, $this->subscriber::getSubscribedEvents());

    foreach ($expectedSubscriptions as $method) {
      static::assertTrue(method_exists($this->subscriber, $method));
    }
  }

  public function testOnCreated(): void {
    $clearingProcessBundle = ClearingProcessBundleFactory::create();
    $event = new ClearingProcessCreatedEvent($clearingProcessBundle);
    $task = FundingTaskFactory::create();

    $this->taskCreatorMock->expects(static::once())->method('createTasksOnNew')
      ->willReturn([$task]);
    $this->taskManagerMock->expects(static::once())->method('addTask')
      ->with($task)
      ->willReturn($task);

    $this->subscriber->onCreated($event);
  }

  public function testOnCreatedWithoutCreators(): void {
    $clearingProcessBundle = ClearingProcessBundleFactory::create(
      [],
      [],
      [],
      ['name' => 'SomeCaseType']
    );
    $event = new ClearingProcessCreatedEvent($clearingProcessBundle);

    $this->taskCreatorMock->expects(static::never())->method('createTasksOnNew');

    $this->subscriber->onCreated($event);
  }

  public function testOnUpdated(): void {
    $clearingProcessBundle = ClearingProcessBundleFactory::create();
    $previousClearingProcess = ClearingProcessFactory::create();
    $event = new ClearingProcessUpdatedEvent($previousClearingProcess, $clearingProcessBundle);

    $existingTask = FundingTaskFactory::create(['subject' => 'Existing Task']);
    $newTask = FundingTaskFactory::create(['subject' => 'New Task']);

    $this->taskManagerMock->expects(static::once())->method('getOpenTasks')
      ->with(ActivityTypeNames::CLEARING_PROCESS_TASK, $clearingProcessBundle->getClearingProcess()->getId())
      ->willReturn([$existingTask]);
    $this->taskModifierMock->expects(static::once())->method('modifyTask')
      ->with($existingTask, $clearingProcessBundle, $previousClearingProcess)
      ->willReturn(TRUE);
    $this->taskManagerMock->expects(static::once())->method('updateTask')->with($existingTask);

    $this->taskCreatorMock->expects(static::once())->method('createTasksOnChange')
      ->with($clearingProcessBundle, $previousClearingProcess)
      ->willReturn([$newTask]);
    $this->taskManagerMock->expects(static::once())->method('addTask')
      ->with($newTask)
      ->willReturn($newTask);

    $this->subscriber->onUpdated($event);
  }

  public function testOnUpdatedWithoutCreatorsOrModifiers(): void {
    $clearingProcessBundle = ClearingProcessBundleFactory::create(
      [],
      [],
      [],
      ['name' => 'SomeCaseType']
    );
    $previousClearingProcess = ClearingProcessFactory::create();
    $event = new ClearingProcessUpdatedEvent($previousClearingProcess, $clearingProcessBundle);

    $existingTask = FundingTaskFactory::create(['subject' => 'Existing Task']);

    $this->taskManagerMock->expects(static::once())->method('getOpenTasks')
      ->with(ActivityTypeNames::CLEARING_PROCESS_TASK, $clearingProcessBundle->getClearingProcess()->getId())
      ->willReturn([$existingTask]);
    $this->taskModifierMock->expects(static::never())->method('modifyTask');
    $this->taskManagerMock->expects(static::never())->method('updateTask');
    $this->taskCreatorMock->expects(static::never())->method('createTasksOnChange');

    $this->subscriber->onUpdated($event);
  }

}
