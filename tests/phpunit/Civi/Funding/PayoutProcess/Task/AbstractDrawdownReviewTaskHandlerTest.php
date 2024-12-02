<?php
declare(strict_types = 1);

namespace Civi\Funding\PayoutProcess\Task;

use Civi\Funding\ActivityStatusNames;
use Civi\Funding\Entity\FundingTaskEntity;
use Civi\Funding\EntityFactory\DrawdownBundleFactory;
use Civi\Funding\EntityFactory\DrawdownFactory;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\PayoutProcess\Task\AbstractDrawdownReviewTaskHandler
 */
final class AbstractDrawdownReviewTaskHandlerTest extends TestCase {

  private AbstractDrawdownReviewTaskHandler $taskHandler;

  protected function setUp(): void {
    parent::setUp();
    $this->taskHandler = $this->getMockForAbstractClass(AbstractDrawdownReviewTaskHandler::class);
  }

  public function testCreateTasksOnNewStatusNew(): void {
    $drawdownBundle = DrawdownBundleFactory::create(['id' => 99, 'status' => 'new']);

    $tasks = [...$this->taskHandler->createTasksOnNew($drawdownBundle)];
    static::assertEquals([
      FundingTaskEntity::newTask([
        'subject' => 'Review drawdown',
        'affected_identifier' => $drawdownBundle->getFundingCase()->getIdentifier(),
        'required_permissions' => ['review_drawdown'],
        'type' => 'review',
        'funding_case_id' => $drawdownBundle->getFundingCase()->getId(),
        'payout_process_id' => $drawdownBundle->getPayoutProcess()->getId(),
        'drawdown_id' => $drawdownBundle->getDrawdown()->getId(),
      ]),
    ], $tasks);
  }

  public function testCreateTasksOnNewStatusNotNew(): void {
    $drawdownBundle = DrawdownBundleFactory::create(['id' => 99, 'status' => 'test']);

    $tasks = [...$this->taskHandler->createTasksOnNew($drawdownBundle)];
    static::assertSame([], $tasks);
  }

  public function testCreateTasksOnChangeStatusNotNew(): void {
    $previousApplication = DrawdownFactory::create(['id' => 99, 'status' => 'new']);
    $drawdownBundle = DrawdownBundleFactory::create(['id' => 99, 'status' => 'test']);

    $tasks = [...$this->taskHandler->createTasksOnChange($drawdownBundle, $previousApplication)];
    static::assertSame([], $tasks);
  }

  public function testModifyStatusNotNew(): void {
    $previousDrawdown = DrawdownFactory::create(['id' => 99, 'status' => 'new']);
    $drawdownBundle = DrawdownBundleFactory::create(['id' => 99, 'status' => 'test']);

    $task = FundingTaskEntity::newTask([
      'subject' => 'Complete and apply application',
      'affected_identifier' => $drawdownBundle->getFundingCase()->getIdentifier(),
      'required_permissions' => ['review_drawdown'],
      'type' => 'review',
      'funding_case_id' => $drawdownBundle->getFundingCase()->getId(),
      'payout_process_id' => $drawdownBundle->getPayoutProcess()->getId(),
      'drawdown_id' => $previousDrawdown->getId(),
    ]);

    static::assertTrue($this->taskHandler->modifyTask($task, $drawdownBundle, $previousDrawdown));
    static::assertSame(ActivityStatusNames::COMPLETED, $task->getStatusName());
  }

  public function testModifyStatusStillNew(): void {
    $previousDrawdown = DrawdownFactory::create(['id' => 99, 'status' => 'new']);
    $drawdownBundle = DrawdownBundleFactory::create(['id' => 99, 'status' => 'new']);

    $task = FundingTaskEntity::newTask([
      'subject' => 'Complete and apply application',
      'affected_identifier' => $drawdownBundle->getFundingCase()->getIdentifier(),
      'required_permissions' => ['review_drawdown'],
      'type' => 'review',
      'funding_case_id' => $drawdownBundle->getFundingCase()->getId(),
      'payout_process_id' => $drawdownBundle->getPayoutProcess()->getId(),
      'drawdown_id' => $previousDrawdown->getId(),
    ]);

    static::assertFalse($this->taskHandler->modifyTask($task, $drawdownBundle, $previousDrawdown));
    static::assertSame(ActivityStatusNames::SCHEDULED, $task->getStatusName());
  }

  public function testModifyTaskDifferentTaskType(): void {
    $previousDrawdown = DrawdownFactory::create(['id' => 99, 'status' => 'new']);
    $drawdownBundle = DrawdownBundleFactory::create(['id' => 99, 'status' => 'new']);

    $task = FundingTaskEntity::newTask([
      'subject' => 'Some Task',
      'affected_identifier' => $drawdownBundle->getFundingCase()->getIdentifier(),
      'required_permissions' => ['review_drawdown'],
      'type' => 'some_type',
      'funding_case_id' => $drawdownBundle->getFundingCase()->getId(),
      'payout_process_id' => $drawdownBundle->getPayoutProcess()->getId(),
      'drawdown_id' => $previousDrawdown->getId(),
    ]);

    static::assertFalse($this->taskHandler->modifyTask($task, $drawdownBundle, $previousDrawdown));
    static::assertSame(ActivityStatusNames::SCHEDULED, $task->getStatusName());
  }

}
