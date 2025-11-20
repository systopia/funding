<?php
declare(strict_types = 1);

namespace Civi\Funding\ApplicationProcess\Task;

use Civi\Funding\ActivityStatusNames;
use Civi\Funding\Entity\FundingTaskEntity;
use Civi\Funding\EntityFactory\ApplicationProcessBundleFactory;
use Civi\Funding\EntityFactory\ApplicationProcessFactory;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\ApplicationProcess\Task\AbstractApplicationApplyTaskHandler
 */
final class AbstractApplicationApplyTaskHandlerTest extends TestCase {

  private AbstractApplicationApplyTaskHandler $taskHandler;

  protected function setUp(): void {
    parent::setUp();
    $this->taskHandler = $this->getMockForAbstractClass(AbstractApplicationApplyTaskHandler::class);
  }

  /**
   * @phpstan-return iterable<array{string}>
   */
  public function provideAppliableStatus(): iterable {
    yield ['new'];
    yield ['draft'];
    yield ['rework'];
  }

  /**
   * @dataProvider provideAppliableStatus
   */
  public function testCreateTasksOnNewStatusAppliable(string $appliableStatus): void {
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle([
      'status' => $appliableStatus,
    ]);

    $tasks = [...$this->taskHandler->createTasksOnNew($applicationProcessBundle)];
    static::assertEquals([
      FundingTaskEntity::newTask([
        'subject' => 'Complete and Apply Application',
        'affected_identifier' => $applicationProcessBundle->getApplicationProcess()->getIdentifier(),
        'required_permissions' => ['application_apply'],
        'type' => 'apply',
        'funding_case_id' => $applicationProcessBundle->getFundingCase()->getId(),
        'application_process_id' => $applicationProcessBundle->getApplicationProcess()->getId(),
      ]),
    ], $tasks);
  }

  public function testCreateTasksOnNewStatusNotAppliable(): void {
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle(['status' => 'test']);

    $tasks = [...$this->taskHandler->createTasksOnNew($applicationProcessBundle)];
    static::assertSame([], $tasks);
  }

  /**
   * @dataProvider provideAppliableStatus
   */
  public function testCreateTasksOnChangeStatusAppliable(string $appliableStatus): void {
    $previousApplication = ApplicationProcessFactory::createApplicationProcess(['status' => 'old']);
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle([
      'status' => $appliableStatus,
    ]);

    $tasks = [...$this->taskHandler->createTasksOnChange($applicationProcessBundle, $previousApplication)];
    static::assertEquals([
      FundingTaskEntity::newTask([
        'subject' => 'Complete and Apply Application',
        'affected_identifier' => $applicationProcessBundle->getApplicationProcess()->getIdentifier(),
        'required_permissions' => ['application_apply'],
        'type' => 'apply',
        'funding_case_id' => $applicationProcessBundle->getFundingCase()->getId(),
        'application_process_id' => $applicationProcessBundle->getApplicationProcess()->getId(),
      ]),
    ], $tasks);
  }

  public function testCreateTasksOnChangeStatusNotAppliable(): void {
    $previousApplication = ApplicationProcessFactory::createApplicationProcess(['status' => 'new']);
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle([
      'status' => 'test',
    ]);

    $tasks = [...$this->taskHandler->createTasksOnChange($applicationProcessBundle, $previousApplication)];
    static::assertSame([], $tasks);
  }

  public function testModifyStatusNotAppliable(): void {
    $previousApplicationProcess = ApplicationProcessFactory::createApplicationProcess(['status' => 'new']);
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle([
      'status' => 'test',
    ]);

    $task = FundingTaskEntity::newTask([
      'subject' => 'Complete and Apply Application',
      'affected_identifier' => $previousApplicationProcess->getIdentifier(),
      'required_permissions' => ['application_apply'],
      'type' => 'apply',
      'funding_case_id' => $previousApplicationProcess->getId(),
      'application_process_id' => $previousApplicationProcess->getId(),
    ]);

    static::assertTrue($this->taskHandler->modifyTask($task, $applicationProcessBundle, $previousApplicationProcess));
    static::assertSame(ActivityStatusNames::COMPLETED, $task->getStatusName());
  }

  /**
   * @dataProvider provideAppliableStatus
   */
  public function testModifyStatusStillAppliable(string $appliableStatus): void {
    $previousApplicationProcess = ApplicationProcessFactory::createApplicationProcess(['status' => 'new']);
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle([
      'status' => $appliableStatus,
    ]);

    $task = FundingTaskEntity::newTask([
      'subject' => 'Complete and Apply Application',
      'affected_identifier' => $previousApplicationProcess->getIdentifier(),
      'required_permissions' => ['application_apply'],
      'type' => 'apply',
      'funding_case_id' => $previousApplicationProcess->getId(),
      'application_process_id' => $previousApplicationProcess->getId(),
    ]);

    static::assertFalse($this->taskHandler->modifyTask($task, $applicationProcessBundle, $previousApplicationProcess));
    static::assertSame(ActivityStatusNames::SCHEDULED, $task->getStatusName());
  }

  public function testModifyTaskDifferentTaskType(): void {
    $previousApplicationProcess = ApplicationProcessFactory::createApplicationProcess(['status' => 'review']);
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle([
      'status' => 'draft',
    ]);

    $task = FundingTaskEntity::newTask([
      'subject' => 'Some Task',
      'affected_identifier' => $previousApplicationProcess->getIdentifier(),
      'required_permissions' => ['application_apply'],
      'type' => 'some_type',
      'funding_case_id' => $previousApplicationProcess->getId(),
      'application_process_id' => $previousApplicationProcess->getId(),
    ]);

    static::assertFalse($this->taskHandler->modifyTask($task, $applicationProcessBundle, $previousApplicationProcess));
    static::assertSame(ActivityStatusNames::SCHEDULED, $task->getStatusName());
  }

}
