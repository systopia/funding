<?php
declare(strict_types = 1);

namespace Civi\Funding\ClearingProcess\Task;

use Civi\Funding\ApplicationProcess\ApplicationProcessManager;
use Civi\Funding\ClearingProcess\ClearingProcessManager;
use Civi\Funding\ClearingProcess\ClearingProcessPermissions;
use Civi\Funding\Entity\FundingTaskEntity;
use Civi\Funding\EntityFactory\ApplicationProcessFactory;
use Civi\Funding\EntityFactory\ClearingProcessFactory;
use Civi\Funding\EntityFactory\FundingCaseBundleFactory;
use Civi\Funding\EntityFactory\FundingCaseFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\ClearingProcess\Task\AbstractClearingCreateTaskCreatorOnFundingCaseChange
 */
final class AbstractClearingCreateTaskCreatorOnFundingCaseChangeTest extends TestCase {

  /**
   * @var \Civi\Funding\ApplicationProcess\ApplicationProcessManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $applicationProcessManagerMock;

  /**
   * @var \Civi\Funding\ClearingProcess\ClearingProcessManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $clearingProcessManagerMock;

  private AbstractClearingCreateTaskCreatorOnFundingCaseChange $taskCreator;

  protected function setUp(): void {
    parent::setUp();
    $this->applicationProcessManagerMock = $this->createMock(ApplicationProcessManager::class);
    $this->clearingProcessManagerMock = $this->createMock(ClearingProcessManager::class);
    $this->taskCreator = $this->getMockForAbstractClass(AbstractClearingCreateTaskCreatorOnFundingCaseChange::class, [
      $this->applicationProcessManagerMock,
      $this->clearingProcessManagerMock,
    ], '', TRUE, TRUE, TRUE, ['getDueDate']);
    $this->taskCreator->method('getDueDate')->willReturn(new \DateTime('2000-01-02'));
  }

  public function testCreateTasksOnChangeApproved(): void {
    $fundingCaseBundle = FundingCaseBundleFactory::create(['amount_approved' => 1.2]);
    $previousFundingCase = FundingCaseFactory::createFundingCase(['amount_approved' => NULL]);
    $eligibleApplicationProcess = ApplicationProcessFactory::createApplicationProcess([
      'id' => 77,
      'is_eligible' => TRUE,
    ]);
    $nonEligibleApplicationProcess = ApplicationProcessFactory::createApplicationProcess([
      'id' => 88,
      'is_eligible' => FALSE,
    ]);
    $this->applicationProcessManagerMock->expects(static::once())->method('getByFundingCaseId')
      ->with($fundingCaseBundle->getFundingCase()->getId())
      ->willReturn([$nonEligibleApplicationProcess, $eligibleApplicationProcess]);

    $clearingProcess = ClearingProcessFactory::create(['status' => 'not-started']);
    $this->clearingProcessManagerMock->expects(static::once())->method('getByApplicationProcessId')
      ->with($eligibleApplicationProcess->getId())
      ->willReturn($clearingProcess);

    static::assertEquals([
      FundingTaskEntity::newTask([
        'subject' => 'Create Clearing',
        'affected_identifier' => $eligibleApplicationProcess->getIdentifier(),
        'required_permissions' => [
          ClearingProcessPermissions::CLEARING_APPLY,
          ClearingProcessPermissions::CLEARING_MODIFY,
        ],
        'type' => 'create',
        'funding_case_id' => $fundingCaseBundle->getFundingCase()->getId(),
        'application_process_id' => $eligibleApplicationProcess->getId(),
        'clearing_process_id' => $clearingProcess->getId(),
        'due_date' => new \DateTime('2000-01-02'),
      ]),
    ], [...$this->taskCreator->createTasksOnChange($fundingCaseBundle, $previousFundingCase)]);
  }

  public function testCreateTasksOnChangeAmountApproved(): void {
    $fundingCaseBundle = FundingCaseBundleFactory::create(['amount_approved' => 1.2]);
    $previousFundingCase = FundingCaseFactory::createFundingCase(['amount_approved' => 3.4]);
    $this->applicationProcessManagerMock->expects(static::never())->method('getByFundingCaseId');

    static::assertSame([], [...$this->taskCreator->createTasksOnChange($fundingCaseBundle, $previousFundingCase)]);
  }

  public function testCreateTasksOnNew(): void {
    $fundingCaseBundle = FundingCaseBundleFactory::create();
    static::assertSame([], [...$this->taskCreator->createTasksOnNew($fundingCaseBundle)]);
  }

}
