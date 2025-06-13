<?php
declare(strict_types = 1);

namespace Civi\Funding\Task\EventSubscriber;

use Civi\Funding\ActivityTypeNames;
use Civi\Funding\EntityFactory\ApplicationProcessBundleFactory;
use Civi\Funding\EntityFactory\ApplicationProcessFactory;
use Civi\Funding\EntityFactory\FundingCaseTypeFactory;
use Civi\Funding\EntityFactory\FundingTaskFactory;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessCreatedEvent;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessUpdatedEvent;
use Civi\Funding\Task\Creator\ApplicationProcessTaskCreatorInterface;
use Civi\Funding\Task\FundingTaskManager;
use Civi\Funding\Task\Modifier\ApplicationProcessTaskModifierInterface;
use Civi\RemoteTools\Api4\Query\Comparison;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\Task\EventSubscriber\ApplicationProcessTaskSubscriber
 */
final class ApplicationProcessTaskSubscriberTest extends TestCase {

  /**
   * @var \Civi\Funding\Task\EventSubscriber\ApplicationProcessTaskSubscriber
   */
  private ApplicationProcessTaskSubscriber $subscriber;

  /**
   * @var \Civi\Funding\Task\Creator\ApplicationProcessTaskCreatorInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $taskCreatorMock;

  /**
   * @var \Civi\Funding\Task\FundingTaskManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $taskManagerMock;

  /**
   * @var \Civi\Funding\Task\Modifier\ApplicationProcessTaskModifierInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $taskModifierMock;

  protected function setUp(): void {
    parent::setUp();
    $this->taskManagerMock = $this->createMock(FundingTaskManager::class);
    $this->taskCreatorMock = $this->createMock(ApplicationProcessTaskCreatorInterface::class);
    $this->taskModifierMock = $this->createMock(ApplicationProcessTaskModifierInterface::class);
    $this->subscriber = new ApplicationProcessTaskSubscriber(
      $this->taskManagerMock,
      [FundingCaseTypeFactory::DEFAULT_NAME => [$this->taskCreatorMock]],
      [FundingCaseTypeFactory::DEFAULT_NAME => [$this->taskModifierMock]]
    );

    $this->taskModifierMock->method('getActivityTypeName')->willReturn(ActivityTypeNames::APPLICATION_PROCESS_TASK);
  }

  public function testGetSubscribedEvents(): void {
    $expectedSubscriptions = [
      ApplicationProcessCreatedEvent::class => 'onCreated',
      ApplicationProcessUpdatedEvent::class => 'onUpdated',
    ];

    static::assertEquals($expectedSubscriptions, $this->subscriber::getSubscribedEvents());

    foreach ($expectedSubscriptions as $method) {
      static::assertTrue(method_exists($this->subscriber, $method));
    }
  }

  public function testOnCreated(): void {
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle();
    $event = new ApplicationProcessCreatedEvent(12, $applicationProcessBundle);
    $task = FundingTaskFactory::create();

    $this->taskCreatorMock->expects(static::once())->method('createTasksOnNew')
      ->willReturn([$task]);
    $this->taskManagerMock->expects(static::once())->method('addTask')
      ->with($task)
      ->willReturn($task);

    $this->subscriber->onCreated($event);
  }

  public function testOnCreatedWithoutCreators(): void {
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle(
      [],
      [],
      ['name' => 'SomeCaseType']
    );
    $event = new ApplicationProcessCreatedEvent(12, $applicationProcessBundle);

    $this->taskCreatorMock->expects(static::never())->method('createTasksOnNew');

    $this->subscriber->onCreated($event);
  }

  public function testOnUpdated(): void {
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle();
    $previousApplicationProcess = ApplicationProcessFactory::createApplicationProcess();
    $event = new ApplicationProcessUpdatedEvent(12, $previousApplicationProcess, $applicationProcessBundle);

    $existingTask = FundingTaskFactory::create(['subject' => 'Existing Task']);
    $newTask = FundingTaskFactory::create(['subject' => 'New Task']);

    $this->taskManagerMock->expects(static::once())->method('getOpenTasksBy')
      ->with(ActivityTypeNames::APPLICATION_PROCESS_TASK, Comparison::new(
        'funding_application_process_task.application_process_id',
        '=',
        $applicationProcessBundle->getApplicationProcess()->getId()
      ))->willReturn([$existingTask]);
    $this->taskModifierMock->expects(static::once())->method('modifyTask')
      ->with($existingTask, $applicationProcessBundle, $previousApplicationProcess)
      ->willReturn(TRUE);
    $this->taskManagerMock->expects(static::once())->method('updateTask')->with($existingTask);

    $this->taskCreatorMock->expects(static::once())->method('createTasksOnChange')
      ->with($applicationProcessBundle, $previousApplicationProcess)
      ->willReturn([$newTask]);
    $this->taskManagerMock->expects(static::once())->method('addTask')
      ->with($newTask)
      ->willReturn($newTask);

    $this->subscriber->onUpdated($event);
  }

  public function testOnUpdatedWithoutCreatorsOrModifiers(): void {
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle(
      [],
      [],
      ['name' => 'SomeCaseType']
    );
    $previousApplicationProcess = ApplicationProcessFactory::createApplicationProcess();
    $event = new ApplicationProcessUpdatedEvent(12, $previousApplicationProcess, $applicationProcessBundle);

    $this->taskManagerMock->expects(static::never())->method('getOpenTasksBy');
    $this->taskCreatorMock->expects(static::never())->method('createTasksOnChange');

    $this->subscriber->onUpdated($event);
  }

}
