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
 * @covers \Civi\Funding\ClearingProcess\Task\AbstractClearingReviewCalculativeTaskHandler
 */
final class AbstractClearingReviewCalculativeTaskHandlerTest extends TestCase {

  private AbstractClearingReviewCalculativeTaskHandler $taskHandler;

  protected function setUp(): void {
    parent::setUp();
    $this->taskHandler = $this->getMockForAbstractClass(AbstractClearingReviewCalculativeTaskHandler::class);
  }

  /**
   * @dataProvider provideReviewableStatusChange
   */
  public function testCreateTasksOnChangeReviewRequested(string $oldStatus, string $newStatus): void {
    $clearingProcessBundle = ClearingProcessBundleFactory::create([
      'status' => $newStatus,
      'reviewer_calc_contact_id' => 123,
    ]);
    $previousClearingProcess = ClearingProcessFactory::create(['status' => $oldStatus]);

    static::assertEquals([
      FundingTaskEntity::newTask([
        'subject' => 'Review Clearing (calculative)',
        'affected_identifier' => $clearingProcessBundle->getApplicationProcess()->getIdentifier(),
        'required_permissions' => [ClearingProcessPermissions::REVIEW_CALCULATIVE],
        'type' => 'review_calculative',
        'funding_case_id' => $clearingProcessBundle->getFundingCase()->getId(),
        'application_process_id' => $clearingProcessBundle->getApplicationProcess()->getId(),
        'clearing_process_id' => $clearingProcessBundle->getClearingProcess()->getId(),
        'assignee_contact_ids' => [123],
      ]),
    ], [...$this->taskHandler->createTasksOnChange($clearingProcessBundle, $previousClearingProcess)]);
  }

  /**
   * @return iterable<array{string, string}>
   */
  public static function provideReviewableStatusChange(): iterable {
    yield ['draft', 'review-requested'];
    yield ['rework', 'rework-review-requested'];
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
      'subject' => 'Review Clearing (calculative)',
      'affected_identifier' => $clearingProcessBundle->getApplicationProcess()->getIdentifier(),
      'required_permissions' => [ClearingProcessPermissions::REVIEW_CALCULATIVE],
      'type' => 'review_calculative',
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
      'is_review_calculative' => TRUE,
    ]);
    $previousClearingProcess = ClearingProcessFactory::create(['status' => 'review']);

    $task = FundingTaskEntity::newTask([
      'subject' => 'Review Clearing (calculative)',
      'affected_identifier' => $clearingProcessBundle->getApplicationProcess()->getIdentifier(),
      'required_permissions' => [ClearingProcessPermissions::REVIEW_CALCULATIVE],
      'type' => 'review_calculative',
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
      'subject' => 'Review Clearing (calculative)',
      'affected_identifier' => $clearingProcessBundle->getApplicationProcess()->getIdentifier(),
      'required_permissions' => [ClearingProcessPermissions::REVIEW_CALCULATIVE],
      'type' => 'review_calculative',
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
      'subject' => 'Some Task',
      'affected_identifier' => $clearingProcessBundle->getApplicationProcess()->getIdentifier(),
      'required_permissions' => [ClearingProcessPermissions::REVIEW_CALCULATIVE],
      'type' => 'some_type',
      'funding_case_id' => $clearingProcessBundle->getFundingCase()->getId(),
      'application_process_id' => $clearingProcessBundle->getApplicationProcess()->getId(),
      'clearing_process_id' => $clearingProcessBundle->getClearingProcess()->getId(),
      'assignee_contact_ids' => [123],
    ]);

    static::assertFalse($this->taskHandler->modifyTask($task, $clearingProcessBundle, $previousClearingProcess));
    static::assertSame(ActivityStatusNames::SCHEDULED, $task->getStatusName());
  }

}
