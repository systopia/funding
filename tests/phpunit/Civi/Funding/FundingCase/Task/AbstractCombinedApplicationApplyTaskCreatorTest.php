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

  public function testCreateTasksOnChangeStatusAppliable(): void {
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle([
      'status' => 'draft',
      'is_in_work' => TRUE,
    ]);
    $previousApplicationProcess = ApplicationProcessFactory::createApplicationProcess([
      'status' => 'test',
      'is_in_work' => FALSE,
    ]);

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
      'is_in_work' => TRUE,
      'short_description' => 'foo',
    ]);
    $previousApplicationProcess = ApplicationProcessFactory::createApplicationProcess([
      'status' => 'draft',
      'is_in_work' => TRUE,
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
      'is_in_work' => FALSE,
    ]);
    $previousApplicationProcess = ApplicationProcessFactory::createApplicationProcess([
      'status' => 'draft',
      'is_in_work' => TRUE,
    ]);

    static::assertSame(
      [],
      [...$this->taskCreator->createTasksOnChange($applicationProcessBundle, $previousApplicationProcess)]
    );
  }

  public function testCreateTasksOnNewAppliable(): void {
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle([
      'status' => 'draft',
      'is_in_work' => TRUE,
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
      'is_in_work' => FALSE,
    ]);

    static::assertSame([], [...$this->taskCreator->createTasksOnNew($applicationProcessBundle)]);
  }

}
