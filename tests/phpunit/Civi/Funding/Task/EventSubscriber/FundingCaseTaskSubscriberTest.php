<?php
declare(strict_types = 1);

namespace Civi\Funding\Task\EventSubscriber;

use Civi\Funding\ActivityTypeNames;
use Civi\Funding\EntityFactory\FundingCaseBundleFactory;
use Civi\Funding\EntityFactory\FundingCaseFactory;
use Civi\Funding\EntityFactory\FundingCaseTypeFactory;
use Civi\Funding\EntityFactory\FundingTaskFactory;
use Civi\Funding\Event\FundingCase\FundingCaseCreatedEvent;
use Civi\Funding\Event\FundingCase\FundingCaseUpdatedEvent;
use Civi\Funding\Task\Creator\FundingCaseTaskCreatorInterface;
use Civi\Funding\Task\FundingTaskManagerInterface;
use Civi\Funding\Task\Modifier\FundingCaseTaskModifierInterface;
use Civi\RemoteTools\Api4\Query\Comparison;
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

  private FundingCaseTaskCreatorInterface&MockObject $taskCreatorMock;

  private FundingTaskManagerInterface&MockObject $taskManagerMock;

  private FundingCaseTaskModifierInterface&MockObject $taskModifierMock;

  protected function setUp(): void {
    parent::setUp();
    $this->taskManagerMock = $this->createMock(FundingTaskManagerInterface::class);
    $this->taskCreatorMock = $this->createMock(FundingCaseTaskCreatorInterface::class);
    $this->taskModifierMock = $this->createMock(FundingCaseTaskModifierInterface::class);
    $this->subscriber = new FundingCaseTaskSubscriber(
      $this->taskManagerMock,
      [FundingCaseTypeFactory::DEFAULT_NAME => [$this->taskCreatorMock]],
      [FundingCaseTypeFactory::DEFAULT_NAME => [$this->taskModifierMock]]
    );

    $this->taskModifierMock->method('getActivityTypeName')->willReturn(ActivityTypeNames::FUNDING_CASE_TASK);
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
    $fundingCaseBundle = FundingCaseBundleFactory::create();
    $event = new FundingCaseCreatedEvent($fundingCaseBundle);
    $task = FundingTaskFactory::create();

    $this->taskCreatorMock->expects(static::once())->method('createTasksOnNew')
      ->willReturn([$task]);
    $this->taskManagerMock->expects(static::once())->method('addTask')
      ->with($task)
      ->willReturn($task);

    $this->subscriber->onCreated($event);
  }

  public function testOnCreatedWithoutCreators(): void {
    $fundingCaseBundle = FundingCaseBundleFactory::create([], ['name' => 'SomeCaseType']);
    $event = new FundingCaseCreatedEvent($fundingCaseBundle);

    $this->taskCreatorMock->expects(static::never())->method('createTasksOnNew');

    $this->subscriber->onCreated($event);
  }

  public function testOnUpdated(): void {
    $fundingCaseBundle = FundingCaseBundleFactory::create();
    $previousFundingCase = FundingCaseFactory::createFundingCase();
    $event = new FundingCaseUpdatedEvent($previousFundingCase, $fundingCaseBundle);

    $existingTask = FundingTaskFactory::create(['subject' => 'Existing Task']);
    $newTask = FundingTaskFactory::create(['subject' => 'New Task']);

    $this->taskManagerMock->expects(static::once())->method('getOpenTasksBy')
      ->with(ActivityTypeNames::FUNDING_CASE_TASK, Comparison::new(
        'funding_case_task.funding_case_id', '=', $fundingCaseBundle->getFundingCase()->getId())
      )->willReturn([$existingTask]);
    $this->taskModifierMock->expects(static::once())->method('modifyTask')
      ->with($existingTask, $fundingCaseBundle, $previousFundingCase)
      ->willReturn(TRUE);
    $this->taskManagerMock->expects(static::once())->method('updateTask')->with($existingTask);

    $this->taskCreatorMock->expects(static::once())->method('createTasksOnChange')
      ->with($fundingCaseBundle, $previousFundingCase)
      ->willReturn([$newTask]);
    $this->taskManagerMock->expects(static::once())->method('addTask')
      ->with($newTask)
      ->willReturn($newTask);

    $this->subscriber->onUpdated($event);
  }

  public function testOnUpdatedWithoutCreatorsOrModifiers(): void {
    $fundingCaseBundle = FundingCaseBundleFactory::create([], ['name' => 'SomeCaseType']);
    $previousFundingCase = FundingCaseFactory::createFundingCase();
    $event = new FundingCaseUpdatedEvent($previousFundingCase, $fundingCaseBundle);

    $existingTask = FundingTaskFactory::create(['subject' => 'Existing Task']);

    $this->taskManagerMock->expects(static::never())->method('getOpenTasksBy');
    $this->taskCreatorMock->expects(static::never())->method('createTasksOnChange');

    $this->subscriber->onUpdated($event);
  }

}
