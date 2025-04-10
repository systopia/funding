<?php
declare(strict_types = 1);

namespace Civi\Funding\ApplicationProcess\Task;

use Civi\Funding\ActivityStatusNames;
use Civi\Funding\ApplicationProcess\ApplicationProcessPermissions;
use Civi\Funding\Entity\FundingTaskEntity;
use Civi\Funding\EntityFactory\ApplicationProcessBundleFactory;
use Civi\Funding\EntityFactory\ApplicationProcessFactory;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\ApplicationProcess\Task\AbstractApplicationReviewContentTaskHandler
 */
final class AbstractApplicationReviewContentTaskHandlerTest extends TestCase {

  private AbstractApplicationReviewContentTaskHandler $taskHandler;

  protected function setUp(): void {
    parent::setUp();
    $this->taskHandler = $this->getMockForAbstractClass(
      AbstractApplicationReviewContentTaskHandler::class
    );
  }

  public function testCreateTasksOnChangeStatusApplied(): void {
    $previousApplicationProcess = ApplicationProcessFactory::createApplicationProcess([
      'status' => 'draft',
      'is_in_work' => TRUE,
      'is_eligible' => NULL,
    ]);
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle([
      'status' => 'applied',
      'reviewer_cont_contact_id' => 123,
      'is_in_work' => FALSE,
      'is_eligible' => NULL,
    ]);

    $tasks = [...$this->taskHandler->createTasksOnChange($applicationProcessBundle, $previousApplicationProcess)];
    static::assertEquals([
      FundingTaskEntity::newTask([
        'subject' => 'Review Application (content)',
        'affected_identifier' => $applicationProcessBundle->getApplicationProcess()->getIdentifier(),
        'required_permissions' => [ApplicationProcessPermissions::REVIEW_CONTENT],
        'type' => 'review_content',
        'funding_case_id' => $applicationProcessBundle->getFundingCase()->getId(),
        'application_process_id' => $applicationProcessBundle->getApplicationProcess()->getId(),
        'assignee_contact_ids' => [123],
      ]),
    ], $tasks);
  }

  public function testCreateTasksOnChangeStatusReviewWithResult(): void {
    $previousApplicationProcess = ApplicationProcessFactory::createApplicationProcess([
      'status' => 'applied',
      'is_in_work' => FALSE,
      'is_eligible' => NULL,
    ]);
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle([
      'status' => 'review',
      'is_review_content' => FALSE,
      'is_in_work' => FALSE,
      'is_eligible' => NULL,
    ]);

    $tasks = [...$this->taskHandler->createTasksOnChange($applicationProcessBundle, $previousApplicationProcess)];
    static::assertSame([], $tasks);
  }

  public function testCreateTasksOnChangeStatusNonReview(): void {
    $previousApplicationProcess = ApplicationProcessFactory::createApplicationProcess([
      'status' => 'review',
      'is_in_work' => FALSE,
      'is_eligible' => NULL,
    ]);
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle([
      'status' => 'draft',
      'is_in_work' => TRUE,
      'is_eligible' => NULL,
    ]);

    $tasks = [...$this->taskHandler->createTasksOnChange($applicationProcessBundle, $previousApplicationProcess)];
    static::assertSame([], $tasks);
  }

  public function testCreateTasksOnNewStatusApplied(): void {
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle([
      'status' => 'applied',
      'is_in_work' => FALSE,
      'is_eligible' => NULL,
    ]);

    $tasks = [...$this->taskHandler->createTasksOnNew($applicationProcessBundle)];
    static::assertEquals([
      FundingTaskEntity::newTask([
        'subject' => 'Review Application (content)',
        'affected_identifier' => $applicationProcessBundle->getApplicationProcess()->getIdentifier(),
        'required_permissions' => [ApplicationProcessPermissions::REVIEW_CONTENT],
        'type' => 'review_content',
        'funding_case_id' => $applicationProcessBundle->getFundingCase()->getId(),
        'application_process_id' => $applicationProcessBundle->getApplicationProcess()->getId(),
        'assignee_contact_ids' => [],
      ]),
    ], $tasks);
  }

  public function testCreateTasksOnNewInWork(): void {
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle([
      'status' => 'new',
      'is_in_work' => TRUE,
      'is_eligible' => NULL,
    ]);

    $tasks = [...$this->taskHandler->createTasksOnNew($applicationProcessBundle)];
    static::assertSame([], $tasks);
  }

  public function testModifyTaskStatusChange(): void {
    $previousApplicationProcess = ApplicationProcessFactory::createApplicationProcess([
      'status' => 'review',
      'is_in_work' => FALSE,
      'is_eligible' => NULL,
    ]);
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle([
      'status' => 'rejected',
      'is_in_work' => FALSE,
      'is_eligible' => FALSE,
    ]);

    $task = FundingTaskEntity::newTask([
      'subject' => 'Review Application (content)',
      'affected_identifier' => $previousApplicationProcess->getIdentifier(),
      'required_permissions' => [ApplicationProcessPermissions::REVIEW_CONTENT],
      'type' => 'review_content',
      'funding_case_id' => $previousApplicationProcess->getId(),
      'application_process_id' => $previousApplicationProcess->getId(),
      'assignee_contact_ids' => [],
    ]);

    static::assertTrue($this->taskHandler->modifyTask($task, $applicationProcessBundle, $previousApplicationProcess));
    static::assertSame(ActivityStatusNames::CANCELLED, $task->getStatusName());
  }

  public function testModifyTaskReviewStatusChange(): void {
    $previousApplicationProcess = ApplicationProcessFactory::createApplicationProcess([
      'is_in_work' => FALSE,
      'is_eligible' => NULL,
    ]);
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle([
      'is_review_content' => FALSE,
      'is_in_work' => FALSE,
      'is_eligible' => NULL,
    ]);

    $task = FundingTaskEntity::newTask([
      'subject' => 'Review Application (content)',
      'affected_identifier' => $previousApplicationProcess->getIdentifier(),
      'required_permissions' => [ApplicationProcessPermissions::REVIEW_CONTENT],
      'type' => 'review_content',
      'funding_case_id' => $previousApplicationProcess->getId(),
      'application_process_id' => $previousApplicationProcess->getId(),
      'assignee_contact_ids' => [],
    ]);

    static::assertTrue($this->taskHandler->modifyTask($task, $applicationProcessBundle, $previousApplicationProcess));
    static::assertSame(ActivityStatusNames::COMPLETED, $task->getStatusName());
  }

  public function testModifyTaskReviewerContactChange(): void {
    $previousApplicationProcess = ApplicationProcessFactory::createApplicationProcess([
      'is_in_work' => FALSE,
      'is_eligible' => NULL,
      'reviewer_cont_contact_id' => 123,
    ]);
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle([
      'is_in_work' => FALSE,
      'is_eligible' => NULL,
      'reviewer_cont_contact_id' => 1234,
    ]);

    $task = FundingTaskEntity::newTask([
      'subject' => 'Review Application (content)',
      'affected_identifier' => $previousApplicationProcess->getIdentifier(),
      'required_permissions' => [ApplicationProcessPermissions::REVIEW_CONTENT],
      'type' => 'review_content',
      'funding_case_id' => $previousApplicationProcess->getId(),
      'application_process_id' => $previousApplicationProcess->getId(),
      'assignee_contact_ids' => [123],
    ]);

    static::assertTrue($this->taskHandler->modifyTask($task, $applicationProcessBundle, $previousApplicationProcess));
    static::assertSame(ActivityStatusNames::SCHEDULED, $task->getStatusName());
    static::assertSame([1234], $task->get('assignee_contact_id'));
  }

  public function testModifyTaskReviewStatusChangeStillReview(): void {
    $previousApplicationProcess = ApplicationProcessFactory::createApplicationProcess([
      'status' => 'review1',
      'is_in_work' => FALSE,
      'is_eligible' => NULL,
    ]);
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle([
      'status' => 'review2',
      'is_in_work' => FALSE,
      'is_eligible' => NULL,
    ]);

    $task = FundingTaskEntity::newTask([
      'subject' => 'Review Application (content)',
      'affected_identifier' => $previousApplicationProcess->getIdentifier(),
      'required_permissions' => [ApplicationProcessPermissions::REVIEW_CONTENT],
      'type' => 'review_content',
      'funding_case_id' => $previousApplicationProcess->getId(),
      'application_process_id' => $previousApplicationProcess->getId(),
      'assignee_contact_ids' => [],
    ]);

    static::assertFalse($this->taskHandler->modifyTask($task, $applicationProcessBundle, $previousApplicationProcess));
    static::assertSame(ActivityStatusNames::SCHEDULED, $task->getStatusName());
  }

  public function testModifyTaskDifferentTaskType(): void {
    $previousApplicationProcess = ApplicationProcessFactory::createApplicationProcess([
      'status' => 'applied',
      'is_in_work' => TRUE,
      'is_eligible' => NULL,
    ]);
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle([
      'status' => 'review',
      'is_in_work' => FALSE,
      'is_eligible' => NULL,
    ]);

    $task = FundingTaskEntity::newTask([
      'subject' => 'Some Task',
      'affected_identifier' => $previousApplicationProcess->getIdentifier(),
      'required_permissions' => [ApplicationProcessPermissions::REVIEW_CONTENT],
      'type' => 'some_type',
      'funding_case_id' => $previousApplicationProcess->getId(),
      'application_process_id' => $previousApplicationProcess->getId(),
      'assignee_contact_ids' => [],
    ]);

    static::assertFalse($this->taskHandler->modifyTask($task, $applicationProcessBundle, $previousApplicationProcess));
    static::assertSame(ActivityStatusNames::SCHEDULED, $task->getStatusName());
  }

}
