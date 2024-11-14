<?php
declare(strict_types = 1);

namespace Civi\Funding\ApplicationProcess\Task;

use Civi\Funding\ActivityStatusNames;
use Civi\Funding\ApplicationProcess\ActionStatusInfo\ApplicationProcessActionStatusInfoContainer;
use Civi\Funding\ApplicationProcess\ActionStatusInfo\ApplicationProcessActionStatusInfoInterface;
use Civi\Funding\ApplicationProcess\ApplicationProcessPermissions;
use Civi\Funding\Entity\FundingTaskEntity;
use Civi\Funding\EntityFactory\ApplicationProcessBundleFactory;
use Civi\Funding\EntityFactory\ApplicationProcessFactory;
use Civi\Funding\EntityFactory\FundingCaseTypeFactory;
use Civi\Funding\Mock\Psr\PsrContainer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\ApplicationProcess\Task\AbstractApplicationReviewCalculativeTaskHandler
 */
final class AbstractApplicationReviewCalculativeTaskHandlerTest extends TestCase {

  /**
   * @var \Civi\Funding\ApplicationProcess\ActionStatusInfo\ApplicationProcessActionStatusInfoInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $infoMock;

  private AbstractApplicationReviewCalculativeTaskHandler $taskHandler;

  protected function setUp(): void {
    parent::setUp();
    $this->infoMock = $this->createMock(ApplicationProcessActionStatusInfoInterface::class);
    $infoContainer = new ApplicationProcessActionStatusInfoContainer(new PsrContainer([
      FundingCaseTypeFactory::DEFAULT_NAME => $this->infoMock,
    ]));
    $this->taskHandler = $this->getMockForAbstractClass(
      AbstractApplicationReviewCalculativeTaskHandler::class,
      [$infoContainer]
    );
  }

  public function testCreateTasksOnChangeStatusReview(): void {
    $previousApplicationProcess = ApplicationProcessFactory::createApplicationProcess(['status' => 'applied']);
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle([
      'status' => 'review',
      'reviewer_calc_contact_id' => 123,
    ]);

    $this->infoMock->expects(static::once())->method('isReviewStatus')
      ->with('review')
      ->willReturn(TRUE);

    $tasks = [...$this->taskHandler->createTasksOnChange($applicationProcessBundle, $previousApplicationProcess)];
    static::assertEquals([
      FundingTaskEntity::newTask([
        'subject' => 'Review Funding Application (calculative)',
        'affected_identifier' => $applicationProcessBundle->getApplicationProcess()->getIdentifier(),
        'required_permissions' => [ApplicationProcessPermissions::REVIEW_CALCULATIVE],
        'type' => 'review_calculative',
        'funding_case_id' => $applicationProcessBundle->getFundingCase()->getId(),
        'application_process_id' => $applicationProcessBundle->getApplicationProcess()->getId(),
        'assignee_contact_ids' => [123],
      ]),
    ], $tasks);
  }

  public function testCreateTasksOnChangeStatusReviewWithResult(): void {
    $previousApplicationProcess = ApplicationProcessFactory::createApplicationProcess(['status' => 'applied']);
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle([
      'status' => 'review',
      'is_review_calculative' => FALSE,
    ]);

    $this->infoMock->expects(static::once())->method('isReviewStatus')
      ->with('review')
      ->willReturn(TRUE);

    $tasks = [...$this->taskHandler->createTasksOnChange($applicationProcessBundle, $previousApplicationProcess)];
    static::assertSame([], $tasks);
  }

  public function testCreateTasksOnChangeStatusNonReview(): void {
    $previousApplicationProcess = ApplicationProcessFactory::createApplicationProcess(['status' => 'new']);
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle(
      ['status' => 'applied']
    );

    $this->infoMock->expects(static::once())->method('isReviewStatus')
      ->with('applied')
      ->willReturn(FALSE);

    $tasks = [...$this->taskHandler->createTasksOnChange($applicationProcessBundle, $previousApplicationProcess)];
    static::assertSame([], $tasks);
  }

  public function testCreateTasksOnNewStatusReview(): void {
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle([
      'status' => 'review',
    ]);

    $this->infoMock->expects(static::once())->method('isReviewStatus')
      ->with('review')
      ->willReturn(TRUE);

    $tasks = [...$this->taskHandler->createTasksOnNew($applicationProcessBundle)];
    static::assertEquals([
      FundingTaskEntity::newTask([
        'subject' => 'Review Funding Application (calculative)',
        'affected_identifier' => $applicationProcessBundle->getApplicationProcess()->getIdentifier(),
        'required_permissions' => [ApplicationProcessPermissions::REVIEW_CALCULATIVE],
        'type' => 'review_calculative',
        'funding_case_id' => $applicationProcessBundle->getFundingCase()->getId(),
        'application_process_id' => $applicationProcessBundle->getApplicationProcess()->getId(),
        'assignee_contact_ids' => [],
      ]),
    ], $tasks);
  }

  public function testCreateTasksOnNewStatusNonReview(): void {
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle(
      ['status' => 'applied']
    );

    $this->infoMock->expects(static::once())->method('isReviewStatus')
      ->with('applied')
      ->willReturn(FALSE);

    $tasks = [...$this->taskHandler->createTasksOnNew($applicationProcessBundle)];
    static::assertSame([], $tasks);
  }

  public function testModifyTaskStatusChange(): void {
    $previousApplicationProcess = ApplicationProcessFactory::createApplicationProcess(['status' => 'review']);
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle(
      ['status' => 'rejected']
    );

    $task = FundingTaskEntity::newTask([
      'subject' => 'Review Funding Application (calculative)',
      'affected_identifier' => $previousApplicationProcess->getIdentifier(),
      'required_permissions' => [ApplicationProcessPermissions::REVIEW_CALCULATIVE],
      'type' => 'review_calculative',
      'funding_case_id' => $previousApplicationProcess->getId(),
      'application_process_id' => $previousApplicationProcess->getId(),
      'assignee_contact_ids' => [],
    ]);

    $this->infoMock->expects(static::once())->method('isReviewStatus')
      ->with('rejected')
      ->willReturn(FALSE);

    static::assertTrue($this->taskHandler->modifyTask($task, $applicationProcessBundle, $previousApplicationProcess));
    static::assertSame(ActivityStatusNames::CANCELLED, $task->getStatusName());
  }

  public function testModifyTaskReviewStatusChange(): void {
    $previousApplicationProcess = ApplicationProcessFactory::createApplicationProcess([]);
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle([
      'is_review_calculative' => FALSE,
    ]);

    $task = FundingTaskEntity::newTask([
      'subject' => 'Review Funding Application (calculative)',
      'affected_identifier' => $previousApplicationProcess->getIdentifier(),
      'required_permissions' => [ApplicationProcessPermissions::REVIEW_CALCULATIVE],
      'type' => 'review_calculative',
      'funding_case_id' => $previousApplicationProcess->getId(),
      'application_process_id' => $previousApplicationProcess->getId(),
      'assignee_contact_ids' => [],
    ]);

    $this->infoMock->expects(static::never())->method('isReviewStatus');

    static::assertTrue($this->taskHandler->modifyTask($task, $applicationProcessBundle, $previousApplicationProcess));
    static::assertSame(ActivityStatusNames::COMPLETED, $task->getStatusName());
  }

  public function testModifyTaskReviewerContactChange(): void {
    $previousApplicationProcess = ApplicationProcessFactory::createApplicationProcess([
      'reviewer_calc_contact_id' => 123,
    ]);
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle(
      ['reviewer_calc_contact_id' => 1234]
    );

    $task = FundingTaskEntity::newTask([
      'subject' => 'Review Funding Application (calculative)',
      'affected_identifier' => $previousApplicationProcess->getIdentifier(),
      'required_permissions' => [ApplicationProcessPermissions::REVIEW_CALCULATIVE],
      'type' => 'review_calculative',
      'funding_case_id' => $previousApplicationProcess->getId(),
      'application_process_id' => $previousApplicationProcess->getId(),
      'assignee_contact_ids' => [123],
    ]);

    $this->infoMock->expects(static::never())->method('isReviewStatus');

    static::assertTrue($this->taskHandler->modifyTask($task, $applicationProcessBundle, $previousApplicationProcess));
    static::assertSame(ActivityStatusNames::SCHEDULED, $task->getStatusName());
    static::assertSame([1234], $task->get('assignee_contact_id'));
  }

  public function testModifyTaskReviewStatusChangeStillReview(): void {
    $previousApplicationProcess = ApplicationProcessFactory::createApplicationProcess(['status' => 'review1']);
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle([
      'status' => 'review2',
    ]);

    $task = FundingTaskEntity::newTask([
      'subject' => 'Review Funding Application (calculative)',
      'affected_identifier' => $previousApplicationProcess->getIdentifier(),
      'required_permissions' => [ApplicationProcessPermissions::REVIEW_CALCULATIVE],
      'type' => 'review_calculative',
      'funding_case_id' => $previousApplicationProcess->getId(),
      'application_process_id' => $previousApplicationProcess->getId(),
      'assignee_contact_ids' => [],
    ]);

    $this->infoMock->expects(static::once())->method('isReviewStatus')
      ->with('review2')
      ->willReturn(TRUE);

    static::assertFalse($this->taskHandler->modifyTask($task, $applicationProcessBundle, $previousApplicationProcess));
    static::assertSame(ActivityStatusNames::SCHEDULED, $task->getStatusName());
  }

  public function testModifyTaskDifferentTaskType(): void {
    $previousApplicationProcess = ApplicationProcessFactory::createApplicationProcess(['status' => 'applied']);
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle([
      'status' => 'review',
    ]);

    $task = FundingTaskEntity::newTask([
      'subject' => 'Some Task',
      'affected_identifier' => $previousApplicationProcess->getIdentifier(),
      'required_permissions' => [ApplicationProcessPermissions::REVIEW_CALCULATIVE],
      'type' => 'some_type',
      'funding_case_id' => $previousApplicationProcess->getId(),
      'application_process_id' => $previousApplicationProcess->getId(),
      'assignee_contact_ids' => [],
    ]);

    $this->infoMock->expects(static::never())->method('isReviewStatus');

    static::assertFalse($this->taskHandler->modifyTask($task, $applicationProcessBundle, $previousApplicationProcess));
    static::assertSame(ActivityStatusNames::SCHEDULED, $task->getStatusName());
  }

}
