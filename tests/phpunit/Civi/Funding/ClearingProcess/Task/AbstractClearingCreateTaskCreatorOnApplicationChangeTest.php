<?php
declare(strict_types = 1);

namespace Civi\Funding\ClearingProcess\Task;

use Civi\Funding\ClearingProcess\ClearingProcessManager;
use Civi\Funding\ClearingProcess\ClearingProcessPermissions;
use Civi\Funding\Entity\FundingTaskEntity;
use Civi\Funding\EntityFactory\ApplicationProcessBundleFactory;
use Civi\Funding\EntityFactory\ApplicationProcessFactory;
use Civi\Funding\EntityFactory\ClearingProcessFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\ClearingProcess\Task\AbstractClearingCreateTaskCreatorOnApplicationChange
 */
final class AbstractClearingCreateTaskCreatorOnApplicationChangeTest extends TestCase {

  /**
   * @var \Civi\Funding\ClearingProcess\ClearingProcessManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $clearingProcessManagerMock;

  private AbstractClearingCreateTaskCreatorOnApplicationChange $taskCreator;

  protected function setUp(): void {
    parent::setUp();
    $this->clearingProcessManagerMock = $this->createMock(ClearingProcessManager::class);
    $this->taskCreator = $this->getMockForAbstractClass(AbstractClearingCreateTaskCreatorOnApplicationChange::class, [
      $this->clearingProcessManagerMock,
    ], mockedMethods: ['getDueDate']);
    $this->taskCreator->method('getDueDate')->willReturn(new \DateTime('2000-01-02'));
  }

  public function testCreateTasksOnChangeEligibleApproved(): void {
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle(
      ['is_eligible' => TRUE],
      ['amount_approved' => 1.2]
    );
    $previousApplicationProcess = ApplicationProcessFactory::createApplicationProcess(['is_eligible' => FALSE]);
    $clearingProcess = ClearingProcessFactory::create(['status' => 'not-started']);
    $this->clearingProcessManagerMock->expects(static::once())->method('getByApplicationProcessId')
      ->with($applicationProcessBundle->getApplicationProcess()->getId())
      ->willReturn($clearingProcess);

    static::assertEquals([
      FundingTaskEntity::newTask([
        'subject' => 'Create Clearing',
        'affected_identifier' => $applicationProcessBundle->getApplicationProcess()->getIdentifier(),
        'required_permissions' => [
          ClearingProcessPermissions::CLEARING_APPLY,
          ClearingProcessPermissions::CLEARING_MODIFY,
        ],
        'type' => 'create',
        'funding_case_id' => $applicationProcessBundle->getFundingCase()->getId(),
        'application_process_id' => $applicationProcessBundle->getApplicationProcess()->getId(),
        'clearing_process_id' => $clearingProcess->getId(),
        'due_date' => new \DateTime('2000-01-02'),
      ]),
    ], [...$this->taskCreator->createTasksOnChange($applicationProcessBundle, $previousApplicationProcess)]);
  }

  public function testCreateTasksOnChangeEligibleNotApproved(): void {
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle(
      ['is_eligible' => TRUE],
      ['amount_approved' => NULL]
    );
    $previousApplicationProcess = ApplicationProcessFactory::createApplicationProcess(['is_eligible' => FALSE]);

    static::assertSame([], [...$this->taskCreator->createTasksOnChange(
        $applicationProcessBundle,
        $previousApplicationProcess
      ),
    ]);
  }

  public function testCreateTasksOnNew(): void {
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle([
      'is_eligible' => TRUE,
    ]);
    static::assertSame([], [...$this->taskCreator->createTasksOnNew($applicationProcessBundle)]);
  }

}
