<?php
declare(strict_types = 1);

namespace Civi\Funding\Task\EventSubscriber;

use Civi\Funding\ActivityTypeNames;
use Civi\Funding\EntityFactory\DrawdownBundleFactory;
use Civi\Funding\EntityFactory\DrawdownFactory;
use Civi\Funding\EntityFactory\FundingCaseTypeFactory;
use Civi\Funding\EntityFactory\FundingTaskFactory;
use Civi\Funding\Event\PayoutProcess\DrawdownCreatedEvent;
use Civi\Funding\Event\PayoutProcess\DrawdownDeletedEvent;
use Civi\Funding\Event\PayoutProcess\DrawdownUpdatedEvent;
use Civi\Funding\Task\Creator\DrawdownTaskCreatorInterface;
use Civi\Funding\Task\FundingTaskManagerInterface;
use Civi\Funding\Task\Modifier\DrawdownTaskModifierInterface;
use Civi\RemoteTools\Api4\Query\Comparison;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\Task\EventSubscriber\DrawdownTaskSubscriber
 */
final class DrawdownTaskSubscriberTest extends TestCase {

  /**
   * @var \Civi\Funding\Task\EventSubscriber\DrawdownTaskSubscriber
   */
  private DrawdownTaskSubscriber $subscriber;

  private DrawdownTaskCreatorInterface&MockObject $taskCreatorMock;

  private FundingTaskManagerInterface&MockObject $taskManagerMock;

  private DrawdownTaskModifierInterface&MockObject $taskModifierMock;

  protected function setUp(): void {
    parent::setUp();
    $this->taskManagerMock = $this->createMock(FundingTaskManagerInterface::class);
    $this->taskCreatorMock = $this->createMock(DrawdownTaskCreatorInterface::class);
    $this->taskModifierMock = $this->createMock(DrawdownTaskModifierInterface::class);
    $this->subscriber = new DrawdownTaskSubscriber(
      $this->taskManagerMock,
      [FundingCaseTypeFactory::DEFAULT_NAME => [$this->taskCreatorMock]],
      [FundingCaseTypeFactory::DEFAULT_NAME => [$this->taskModifierMock]]
    );

    $this->taskModifierMock->method('getActivityTypeName')->willReturn(ActivityTypeNames::DRAWDOWN_TASK);
  }

  public function testGetSubscribedEvents(): void {
    $expectedSubscriptions = [
      DrawdownCreatedEvent::class => 'onCreated',
      DrawdownDeletedEvent::class => 'onDeleted',
      DrawdownUpdatedEvent::class => 'onUpdated',
    ];

    static::assertEquals($expectedSubscriptions, $this->subscriber::getSubscribedEvents());

    foreach ($expectedSubscriptions as $method) {
      static::assertTrue(method_exists($this->subscriber, $method));
    }
  }

  public function testOnCreated(): void {
    $drawdownBundle = DrawdownBundleFactory::create();
    $event = new DrawdownCreatedEvent($drawdownBundle);
    $task = FundingTaskFactory::create();

    $this->taskCreatorMock->expects(static::once())->method('createTasksOnNew')
      ->willReturn([$task]);
    $this->taskManagerMock->expects(static::once())->method('addTask')
      ->with($task)
      ->willReturn($task);

    $this->subscriber->onCreated($event);
  }

  public function testOnCreatedWithoutCreators(): void {
    $drawdownBundle = DrawdownBundleFactory::create(
      [],
      [],
      [],
      ['name' => 'SomeCaseType']
    );
    $event = new DrawdownCreatedEvent($drawdownBundle);

    $this->taskCreatorMock->expects(static::never())->method('createTasksOnNew');

    $this->subscriber->onCreated($event);
  }

  public function testOnDeleted(): void {
    $drawdownBundle = DrawdownBundleFactory::create();
    $event = new DrawdownDeletedEvent($drawdownBundle);
    $task = FundingTaskFactory::create();

    $this->taskCreatorMock->expects(static::once())->method('createTasksOnDelete')
      ->with($drawdownBundle)
      ->willReturn([$task]);
    $this->taskManagerMock->expects(static::once())->method('addTask')
      ->with($task)
      ->willReturn($task);

    $this->subscriber->onDeleted($event);
  }

  public function testOnDeletedWithoutCreators(): void {
    $drawdownBundle = DrawdownBundleFactory::create(
      [],
      [],
      [],
      ['name' => 'SomeCaseType']
    );
    $event = new DrawdownDeletedEvent($drawdownBundle);

    $this->taskCreatorMock->expects(static::never())->method('createTasksOnDelete');

    $this->subscriber->onDeleted($event);
  }

  public function testOnUpdated(): void {
    $drawdownBundle = DrawdownBundleFactory::create();
    $previousDrawdown = DrawdownFactory::create();
    $event = new DrawdownUpdatedEvent($previousDrawdown, $drawdownBundle);

    $existingTask = FundingTaskFactory::create(['subject' => 'Existing Task']);
    $newTask = FundingTaskFactory::create(['subject' => 'New Task']);

    $this->taskManagerMock->expects(static::once())->method('getOpenTasksBy')
      ->with(ActivityTypeNames::DRAWDOWN_TASK, Comparison::new(
        'funding_drawdown_task.drawdown_id', '=', $drawdownBundle->getDrawdown()->getId())
      )->willReturn([$existingTask]);
    $this->taskModifierMock->expects(static::once())->method('modifyTask')
      ->with($existingTask, $drawdownBundle, $previousDrawdown)
      ->willReturn(TRUE);
    $this->taskManagerMock->expects(static::once())->method('updateTask')->with($existingTask);

    $this->taskCreatorMock->expects(static::once())->method('createTasksOnChange')
      ->with($drawdownBundle, $previousDrawdown)
      ->willReturn([$newTask]);
    $this->taskManagerMock->expects(static::once())->method('addTask')
      ->with($newTask)
      ->willReturn($newTask);

    $this->subscriber->onUpdated($event);
  }

  public function testOnUpdatedWithoutCreatorsOrModifiers(): void {
    $drawdownBundle = DrawdownBundleFactory::create(
      [],
      [],
      [],
      ['name' => 'SomeCaseType']
    );
    $previousDrawdown = DrawdownFactory::create();
    $event = new DrawdownUpdatedEvent($previousDrawdown, $drawdownBundle);

    $existingTask = FundingTaskFactory::create(['subject' => 'Existing Task']);

    $this->taskManagerMock->expects(static::never())->method('getOpenTasksBy');
    $this->taskCreatorMock->expects(static::never())->method('createTasksOnChange');

    $this->subscriber->onUpdated($event);
  }

}
