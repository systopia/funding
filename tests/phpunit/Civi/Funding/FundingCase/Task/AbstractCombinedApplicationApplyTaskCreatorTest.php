<?php
declare(strict_types = 1);

namespace Civi\Funding\FundingCase\Task;

use Civi\Funding\Entity\FundingTaskEntity;
use Civi\Funding\EntityFactory\ApplicationProcessBundleFactory;
use Civi\Funding\EntityFactory\ApplicationProcessFactory;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\FundingCase\Task\AbstractCombinedApplicationApplyTaskCreator
 */
final class AbstractCombinedApplicationApplyTaskCreatorTest extends TestCase {

  private AbstractCombinedApplicationApplyTaskCreator $taskCreator;

  protected function setUp(): void {
    parent::setUp();
    $this->taskCreator = $this->getMockForAbstractClass(AbstractCombinedApplicationApplyTaskCreator::class);
  }

  /**
   * @dataProvider provideAppliableStatus
   */
  public function testCreateTasksOnChangeStatusAppliable(string $appliableStatus): void {
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle([
      'status' => $appliableStatus,
    ]);
    $previousApplicationProcess = ApplicationProcessFactory::createApplicationProcess(['status' => 'test']);

    static::assertEquals([
      FundingTaskEntity::newTask([
        'subject' => 'Complete and Apply Application',
        'affected_identifier' => $applicationProcessBundle->getFundingCase()->getIdentifier(),
        'required_permissions' => ['application_apply'],
        'type' => 'apply',
        'funding_case_id' => $applicationProcessBundle->getFundingCase()->getId(),
      ]),
    ], [...$this->taskCreator->createTasksOnChange($applicationProcessBundle, $previousApplicationProcess)]);
  }

  public function testCreateTasksOnChangeStatusUnchanged(): void {
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle([
      'status' => 'draft',
      'short_description' => 'foo',
    ]);
    $previousApplicationProcess = ApplicationProcessFactory::createApplicationProcess([
      'status' => 'draft',
      'short_description' => 'bar',
    ]);

    static::assertSame(
      [],
      [...$this->taskCreator->createTasksOnChange($applicationProcessBundle, $previousApplicationProcess)]
    );
  }

  public function testCreateTasksOnChangeStatusNotAppliable(): void {
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle([
      'status' => 'applied',
    ]);
    $previousApplicationProcess = ApplicationProcessFactory::createApplicationProcess([
      'status' => 'draft',
    ]);

    static::assertSame(
      [],
      [...$this->taskCreator->createTasksOnChange($applicationProcessBundle, $previousApplicationProcess)]
    );
  }

  /**
   * @dataProvider provideAppliableStatus
   */
  public function testCreateTasksOnNewAppliable(string $appliableStatus): void {
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle([
      'status' => $appliableStatus,
    ]);

    static::assertEquals([
      FundingTaskEntity::newTask([
        'subject' => 'Complete and Apply Application',
        'affected_identifier' => $applicationProcessBundle->getFundingCase()->getIdentifier(),
        'required_permissions' => ['application_apply'],
        'type' => 'apply',
        'funding_case_id' => $applicationProcessBundle->getFundingCase()->getId(),
      ]),
    ], [...$this->taskCreator->createTasksOnNew($applicationProcessBundle)]);
  }

  public function testCreateTasksOnNewNotAppliable(): void {
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle([
      'status' => 'applied',
    ]);

    static::assertSame([], [...$this->taskCreator->createTasksOnNew($applicationProcessBundle)]);
  }

  /**
   * @phpstan-return iterable<array{string}>
   */
  public function provideAppliableStatus(): iterable {
    yield ['new'];
    yield ['draft'];
    yield ['rework'];
  }

}
