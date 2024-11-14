<?php
declare(strict_types = 1);

namespace Civi\Funding\Task\EventSubscriber;

use Civi\Funding\ActivityTypeNames;
use Civi\Funding\EntityFactory\FundingCaseFactory;
use Civi\Funding\EntityFactory\FundingCaseTypeFactory;
use Civi\Funding\EntityFactory\FundingProgramFactory;
use Civi\Funding\EntityFactory\FundingTaskFactory;
use Civi\Funding\Event\FundingCase\FundingCaseCreatedEvent;
use Civi\Funding\Event\FundingCase\FundingCaseUpdatedEvent;
use Civi\Funding\Task\Creator\FundingCaseTaskCreatorInterface;
use Civi\Funding\Task\FundingTaskManager;
use Civi\Funding\Task\Modifier\FundingCaseTaskModifierInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\Task\EventSubscriber\FundingCaseTaskSubscriber
 */
final class FundingCaseTaskSubscriberTest extends TestCase {

  /**
   * @var \Civi\Funding\Task\EventSubscriber\FundingCaseTaskSubscriber
   */
  private FundingCaseTaskSubscriber $subscriber;

  /**
   * @var \Civi\Funding\Task\Creator\FundingCaseTaskCreatorInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $taskCreatorMock;

  /**
   * @var \Civi\Funding\Task\FundingTaskManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $taskManagerMock;

  /**
   * @var \Civi\Funding\Task\Modifier\FundingCaseTaskModifierInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $taskModifierMock;

  protected function setUp(): void {
    parent::setUp();
    $this->taskManagerMock = $this->createMock(FundingTaskManager::class);
    $this->taskCreatorMock = $this->createMock(FundingCaseTaskCreatorInterface::class);
    $this->taskModifierMock = $this->createMock(FundingCaseTaskModifierInterface::class);
    $this->subscriber = new FundingCaseTaskSubscriber(
      $this->taskManagerMock,
      [FundingCaseTypeFactory::DEFAULT_NAME => [$this->taskCreatorMock]],
      [FundingCaseTypeFactory::DEFAULT_NAME => [$this->taskModifierMock]]
    );
  }

  public function testGetSubscribedEvents(): void {
    $expectedSubscriptions = [
      FundingCaseCreatedEvent::class => 'onCreated',
      FundingCaseUpdatedEvent::class => 'onUpdated',
    ];

    static::assertEquals($expectedSubscriptions, $this->subscriber::getSubscribedEvents());

    foreach ($expectedSubscriptions as $method) {
      static::assertTrue(method_exists($this->subscriber, $method));
    }
  }

  public function testOnCreated(): void {
    $fundingProgram = FundingProgramFactory::createFundingProgram();
    $fundingCaseType = FundingCaseTypeFactory::createFundingCaseType();
    $fundingCase = FundingCaseFactory::createFundingCase();
    $event = new FundingCaseCreatedEvent(12, $fundingCase, $fundingProgram, $fundingCaseType);
    $task = FundingTaskFactory::create();

    $this->taskCreatorMock->expects(static::once())->method('createTasksOnNew')
      ->willReturn([$task]);
    $this->taskManagerMock->expects(static::once())->method('addTask')
      ->with($task)
      ->willReturn($task);

    $this->subscriber->onCreated($event);
  }

  public function testOnCreatedWithoutCreators(): void {
    $fundingProgram = FundingProgramFactory::createFundingProgram();
    $fundingCaseType = FundingCaseTypeFactory::createFundingCaseType(['name' => 'SomeCaseType']);
    $fundingCase = FundingCaseFactory::createFundingCase();
    $event = new FundingCaseCreatedEvent(12, $fundingCase, $fundingProgram, $fundingCaseType);

    $this->taskCreatorMock->expects(static::never())->method('createTasksOnNew');

    $this->subscriber->onCreated($event);
  }

  public function testOnUpdated(): void {
    $fundingCaseType = FundingCaseTypeFactory::createFundingCaseType();
    $fundingCase = FundingCaseFactory::createFundingCase();
    $previousFundingCase = FundingCaseFactory::createFundingCase();
    $event = new FundingCaseUpdatedEvent($previousFundingCase, $fundingCase, $fundingCaseType);

    $existingTask = FundingTaskFactory::create(['subject' => 'Existing Task']);
    $newTask = FundingTaskFactory::create(['subject' => 'New Task']);

    $this->taskManagerMock->expects(static::once())->method('getOpenTasks')
      ->with(ActivityTypeNames::FUNDING_CASE_TASK, $fundingCase->getId())
      ->willReturn([$existingTask]);
    $this->taskModifierMock->expects(static::once())->method('modifyTask')
      ->with($existingTask, $fundingCase, $previousFundingCase)
      ->willReturn(TRUE);
    $this->taskManagerMock->expects(static::once())->method('updateTask')->with($existingTask);

    $this->taskCreatorMock->expects(static::once())->method('createTasksOnChange')
      ->with($fundingCase, $previousFundingCase)
      ->willReturn([$newTask]);
    $this->taskManagerMock->expects(static::once())->method('addTask')
      ->with($newTask)
      ->willReturn($newTask);

    $this->subscriber->onUpdated($event);
  }

  public function testOnUpdatedWithoutCreatorsOrModifiers(): void {
    $fundingCaseType = FundingCaseTypeFactory::createFundingCaseType(['name' => 'SomeCaseType']);
    $fundingCase = FundingCaseFactory::createFundingCase();
    $previousFundingCase = FundingCaseFactory::createFundingCase();
    $event = new FundingCaseUpdatedEvent($previousFundingCase, $fundingCase, $fundingCaseType);

    $existingTask = FundingTaskFactory::create(['subject' => 'Existing Task']);

    $this->taskManagerMock->expects(static::once())->method('getOpenTasks')
      ->with(ActivityTypeNames::FUNDING_CASE_TASK, $fundingCase->getId())
      ->willReturn([$existingTask]);
    $this->taskModifierMock->expects(static::never())->method('modifyTask');
    $this->taskManagerMock->expects(static::never())->method('updateTask');
    $this->taskCreatorMock->expects(static::never())->method('createTasksOnChange');

    $this->subscriber->onUpdated($event);
  }

}
