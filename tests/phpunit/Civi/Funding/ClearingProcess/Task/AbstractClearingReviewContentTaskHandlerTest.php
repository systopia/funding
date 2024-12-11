<?php
declare(strict_types = 1);

namespace Civi\Funding\ClearingProcess\Task;

use Civi\Funding\ActivityStatusNames;
use Civi\Funding\ClearingProcess\ClearingProcessPermissions;
use Civi\Funding\Entity\FundingTaskEntity;
use Civi\Funding\EntityFactory\ClearingProcessBundleFactory;
use Civi\Funding\EntityFactory\ClearingProcessFactory;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\ClearingProcess\Task\AbstractClearingReviewContentTaskHandler
 */
final class AbstractClearingReviewContentTaskHandlerTest extends TestCase {

  private AbstractClearingReviewContentTaskHandler $taskHandler;

  protected function setUp(): void {
    parent::setUp();
    $this->taskHandler = $this->getMockForAbstractClass(AbstractClearingReviewContentTaskHandler::class);
  }

  public function testCreateTasksOnChangeReviewRequested(): void {
    $clearingProcessBundle = ClearingProcessBundleFactory::create([
      'status' => 'review-requested',
      'reviewer_cont_contact_id' => 123,
    ]);
    $previousClearingProcess = ClearingProcessFactory::create(['status' => 'draft']);

    static::assertEquals([
      FundingTaskEntity::newTask([
        'subject' => 'Review Clearing (content)',
        'affected_identifier' => $clearingProcessBundle->getApplicationProcess()->getIdentifier(),
        'required_permissions' => [ClearingProcessPermissions::REVIEW_CONTENT],
        'type' => 'review_content',
        'funding_case_id' => $clearingProcessBundle->getFundingCase()->getId(),
        'application_process_id' => $clearingProcessBundle->getApplicationProcess()->getId(),
        'clearing_process_id' => $clearingProcessBundle->getClearingProcess()->getId(),
        'assignee_contact_ids' => [123],
      ]),
    ], [...$this->taskHandler->createTasksOnChange($clearingProcessBundle, $previousClearingProcess)]);
  }

  public function testCreateTasksOnChangeNotReview(): void {
    $clearingProcessBundle = ClearingProcessBundleFactory::create([
      'status' => 'rejected',
      'reviewer_calc_contact_id' => 123,
    ]);
    $previousClearingProcess = ClearingProcessFactory::create(['status' => 'review']);

    static::assertSame(
      [],
      [...$this->taskHandler->createTasksOnChange($clearingProcessBundle, $previousClearingProcess)]
    );
  }

  public function testCreateTasksOnNew(): void {
    $clearingProcessBundle = ClearingProcessBundleFactory::create([]);

    static::assertSame([], [...$this->taskHandler->createTasksOnNew($clearingProcessBundle)]);
  }

  public function testModifyTaskOnReviewStarted(): void {
    $clearingProcessBundle = ClearingProcessBundleFactory::create(['status' => 'review']);
    $previousClearingProcess = ClearingProcessFactory::create(['status' => 'review-requested']);

    $task = FundingTaskEntity::newTask([
      'subject' => 'Review Clearing (content)',
      'affected_identifier' => $clearingProcessBundle->getApplicationProcess()->getIdentifier(),
      'required_permissions' => [ClearingProcessPermissions::REVIEW_CONTENT],
      'type' => 'review_content',
      'funding_case_id' => $clearingProcessBundle->getFundingCase()->getId(),
      'application_process_id' => $clearingProcessBundle->getApplicationProcess()->getId(),
      'clearing_process_id' => $clearingProcessBundle->getClearingProcess()->getId(),
      'assignee_contact_ids' => [123],
    ]);

    static::assertFalse($this->taskHandler->modifyTask($task, $clearingProcessBundle, $previousClearingProcess));
    static::assertSame(ActivityStatusNames::SCHEDULED, $task->getStatusName());
  }

  public function testModifyTaskOnReviewAccepted(): void {
    $clearingProcessBundle = ClearingProcessBundleFactory::create([
      'status' => 'review',
      'is_review_content' => TRUE,
    ]);
    $previousClearingProcess = ClearingProcessFactory::create(['status' => 'review']);

    $task = FundingTaskEntity::newTask([
      'subject' => 'Review Clearing (content)',
      'affected_identifier' => $clearingProcessBundle->getApplicationProcess()->getIdentifier(),
      'required_permissions' => [ClearingProcessPermissions::REVIEW_CONTENT],
      'type' => 'review_content',
      'funding_case_id' => $clearingProcessBundle->getFundingCase()->getId(),
      'application_process_id' => $clearingProcessBundle->getApplicationProcess()->getId(),
      'clearing_process_id' => $clearingProcessBundle->getClearingProcess()->getId(),
      'assignee_contact_ids' => [123],
    ]);

    static::assertTrue($this->taskHandler->modifyTask($task, $clearingProcessBundle, $previousClearingProcess));
    static::assertSame(ActivityStatusNames::COMPLETED, $task->getStatusName());
  }

  public function testModifyTaskOnReviewNotDecided(): void {
    $clearingProcessBundle = ClearingProcessBundleFactory::create(['status' => 'draft']);
    $previousClearingProcess = ClearingProcessFactory::create(['status' => 'review']);

    $task = FundingTaskEntity::newTask([
      'subject' => 'Review Clearing (content)',
      'affected_identifier' => $clearingProcessBundle->getApplicationProcess()->getIdentifier(),
      'required_permissions' => [ClearingProcessPermissions::REVIEW_CONTENT],
      'type' => 'review_content',
      'funding_case_id' => $clearingProcessBundle->getFundingCase()->getId(),
      'application_process_id' => $clearingProcessBundle->getApplicationProcess()->getId(),
      'clearing_process_id' => $clearingProcessBundle->getClearingProcess()->getId(),
      'assignee_contact_ids' => [123],
    ]);

    static::assertTrue($this->taskHandler->modifyTask($task, $clearingProcessBundle, $previousClearingProcess));
    static::assertSame(ActivityStatusNames::CANCELLED, $task->getStatusName());
  }

  public function testModifyTaskDifferentTaskType(): void {
    $clearingProcessBundle = ClearingProcessBundleFactory::create(['status' => 'draft']);
    $previousClearingProcess = ClearingProcessFactory::create(['status' => 'review']);

    $task = FundingTaskEntity::newTask([
      'subject' => 'Review Clearing (content)',
      'affected_identifier' => $clearingProcessBundle->getApplicationProcess()->getIdentifier(),
      'required_permissions' => [ClearingProcessPermissions::REVIEW_CONTENT],
      'type' => 'review_contentX',
      'funding_case_id' => $clearingProcessBundle->getFundingCase()->getId(),
      'application_process_id' => $clearingProcessBundle->getApplicationProcess()->getId(),
      'clearing_process_id' => $clearingProcessBundle->getClearingProcess()->getId(),
      'assignee_contact_ids' => [123],
    ]);

    static::assertFalse($this->taskHandler->modifyTask($task, $clearingProcessBundle, $previousClearingProcess));
    static::assertSame(ActivityStatusNames::SCHEDULED, $task->getStatusName());
  }

}
