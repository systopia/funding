<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as published by
 *  the Free Software Foundation in version 3.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types = 1);

namespace Civi\Funding\ClearingProcess\Task;

use Civi\Funding\ActivityStatusNames;
use Civi\Funding\ClearingProcess\ClearingProcessPermissions;
use Civi\Funding\Entity\FundingTaskEntity;
use Civi\Funding\EntityFactory\ClearingProcessBundleFactory;
use Civi\Funding\EntityFactory\ClearingProcessFactory;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\ClearingProcess\Task\AbstractClearingReviewFinishTaskHandler
 */
final class AbstractClearingReviewFinishTaskHandlerTest extends TestCase {

  private AbstractClearingReviewFinishTaskHandler $taskHandler;

  protected function setUp(): void {
    parent::setUp();
    $this->taskHandler = $this->getMockForAbstractClass(AbstractClearingReviewFinishTaskHandler::class);
  }

  public function testCreateTasksOnChangeCalculativeAndContentReviewDone(): void {
    $previousClearingProcess = ClearingProcessFactory::create([
      'status' => 'review',
      'is_review_calculative' => TRUE,
      'is_review_content' => NULL,
    ]);
    $clearingProcessBundle = ClearingProcessBundleFactory::create([
      'status' => 'review',
      'is_review_calculative' => TRUE,
      'is_review_content' => FALSE,
      'reviewer_calc_contact_id' => 123,
      'reviewer_cont_contact_id' => 456,
    ]);

    static::assertEquals([
      FundingTaskEntity::newTask([
        'subject' => 'Finish Clearing Review',
        'affected_identifier' => $clearingProcessBundle->getApplicationProcess()->getIdentifier(),
        'required_permissions' => [
          ClearingProcessPermissions::REVIEW_CALCULATIVE,
          ClearingProcessPermissions::REVIEW_CONTENT,
        ],
        'type' => 'review_finish',
        'funding_case_id' => $clearingProcessBundle->getFundingCase()->getId(),
        'application_process_id' => $clearingProcessBundle->getApplicationProcess()->getId(),
        'clearing_process_id' => $clearingProcessBundle->getClearingProcess()->getId(),
        'assignee_contact_ids' => [123, 456],
      ]),
    ], [...$this->taskHandler->createTasksOnChange($clearingProcessBundle, $previousClearingProcess)]);
  }

  public function testCreateTasksOnChangeNotInReviewStatus(): void {
    $previousClearingProcess = ClearingProcessFactory::create([
      'status' => 'test',
      'is_review_calculative' => TRUE,
      'is_review_content' => NULL,
    ]);
    $clearingProcessBundle = ClearingProcessBundleFactory::create([
      'status' => 'test',
      'is_review_calculative' => TRUE,
      'is_review_content' => FALSE,
      'reviewer_calc_contact_id' => 123,
      'reviewer_cont_contact_id' => 456,
    ]);

    static::assertSame(
      [],
      [...$this->taskHandler->createTasksOnChange($clearingProcessBundle, $previousClearingProcess)]
    );
  }

  public function testModifyTaskCancelled(): void {
    $previousClearingProcess = ClearingProcessFactory::create([
      'status' => 'review',
      'is_review_calculative' => TRUE,
      'is_review_content' => TRUE,
    ]);
    $clearingProcessBundle = ClearingProcessBundleFactory::create([
      'status' => 'review',
      'is_review_calculative' => TRUE,
      'is_review_content' => NULL,
    ]);

    $task = FundingTaskEntity::newTask([
      'subject' => 'Finish Clearing Review',
      'affected_identifier' => $clearingProcessBundle->getApplicationProcess()->getIdentifier(),
      'required_permissions' => [
        ClearingProcessPermissions::REVIEW_CALCULATIVE,
        ClearingProcessPermissions::REVIEW_CONTENT,
      ],
      'type' => 'review_finish',
      'funding_case_id' => $previousClearingProcess->getId(),
      'application_process_id' => $clearingProcessBundle->getApplicationProcess()->getId(),
      'clearing_process_id' => $previousClearingProcess->getId(),
    ]);

    static::assertTrue($this->taskHandler->modifyTask($task, $clearingProcessBundle, $previousClearingProcess));
    static::assertSame(ActivityStatusNames::CANCELLED, $task->getStatusName());
  }

  public function testModifyTaskCompleted(): void {
    $previousClearingProcess = ClearingProcessFactory::create(['status' => 'review']);
    $clearingProcessBundle = ClearingProcessBundleFactory::create(
      ['status' => 'rejected']
    );

    $task = FundingTaskEntity::newTask([
      'subject' => 'Finish Clearing Review',
      'affected_identifier' => $clearingProcessBundle->getApplicationProcess()->getIdentifier(),
      'required_permissions' => [
        ClearingProcessPermissions::REVIEW_CALCULATIVE,
        ClearingProcessPermissions::REVIEW_CONTENT,
      ],
      'type' => 'review_finish',
      'funding_case_id' => $previousClearingProcess->getId(),
      'application_process_id' => $clearingProcessBundle->getApplicationProcess()->getId(),
      'clearing_process_id' => $previousClearingProcess->getId(),
    ]);

    static::assertTrue($this->taskHandler->modifyTask($task, $clearingProcessBundle, $previousClearingProcess));
    static::assertSame(ActivityStatusNames::COMPLETED, $task->getStatusName());
  }

  public function testModifyTaskAssigneeChanged(): void {
    $previousClearingProcess = ClearingProcessFactory::create([
      'status' => 'review',
      'is_review_calculative' => TRUE,
      'is_review_content' => TRUE,
      'reviewer_calc_contact_id' => 123,
      'reviewer_cont_contact_id' => NULL,
    ]);
    $clearingProcessBundle = ClearingProcessBundleFactory::create([
      'status' => 'review',
      'is_review_calculative' => TRUE,
      'is_review_content' => TRUE,
      'reviewer_calc_contact_id' => NULL,
      'reviewer_cont_contact_id' => 456,
    ]);

    $task = FundingTaskEntity::newTask([
      'subject' => 'Finish Clearing Review',
      'affected_identifier' => $clearingProcessBundle->getApplicationProcess()->getIdentifier(),
      'required_permissions' => [
        ClearingProcessPermissions::REVIEW_CALCULATIVE,
        ClearingProcessPermissions::REVIEW_CONTENT,
      ],
      'type' => 'review_finish',
      'funding_case_id' => $previousClearingProcess->getId(),
      'application_process_id' => $clearingProcessBundle->getApplicationProcess()->getId(),
      'clearing_process_id' => $previousClearingProcess->getId(),
    ]);

    static::assertTrue($this->taskHandler->modifyTask($task, $clearingProcessBundle, $previousClearingProcess));
    static::assertSame(ActivityStatusNames::SCHEDULED, $task->getStatusName());
    static::assertSame([456], $task->get('assignee_contact_id'));
  }

  public function testModifyTaskDifferentTaskType(): void {
    $previousClearingProcess = ClearingProcessFactory::create([
      'status' => 'review',
      'is_review_calculative' => TRUE,
      'is_review_content' => FALSE,
    ]);
    $clearingProcessBundle = ClearingProcessBundleFactory::create([
      'status' => 'rejected',
      'is_review_calculative' => TRUE,
      'is_review_content' => FALSE,
    ]);

    $task = FundingTaskEntity::newTask([
      'subject' => 'Some Task',
      'affected_identifier' => $clearingProcessBundle->getApplicationProcess()->getIdentifier(),
      'required_permissions' => [
        ClearingProcessPermissions::REVIEW_CALCULATIVE,
        ClearingProcessPermissions::REVIEW_CONTENT,
      ],
      'type' => 'some_type',
      'funding_case_id' => $previousClearingProcess->getId(),
      'application_process_id' => $clearingProcessBundle->getApplicationProcess()->getId(),
      'clearing_process_id' => $previousClearingProcess->getId(),
    ]);

    static::assertFalse($this->taskHandler->modifyTask($task, $clearingProcessBundle, $previousClearingProcess));
    static::assertSame(ActivityStatusNames::SCHEDULED, $task->getStatusName());
  }

}
