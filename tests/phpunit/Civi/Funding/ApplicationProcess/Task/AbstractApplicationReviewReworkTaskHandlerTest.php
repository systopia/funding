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

namespace Civi\Funding\ApplicationProcess\Task;

use Civi\Funding\ActivityStatusNames;
use Civi\Funding\ApplicationProcess\ApplicationProcessPermissions;
use Civi\Funding\Entity\FundingTaskEntity;
use Civi\Funding\EntityFactory\ApplicationProcessBundleFactory;
use Civi\Funding\EntityFactory\ApplicationProcessFactory;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\ApplicationProcess\Task\AbstractApplicationReviewReworkTaskHandler
 */
final class AbstractApplicationReviewReworkTaskHandlerTest extends TestCase {

  private AbstractApplicationReviewReworkTaskHandler $taskHandler;

  protected function setUp(): void {
    parent::setUp();
    $this->taskHandler = new class ()
      extends AbstractApplicationReviewReworkTaskHandler {

      public static function getSupportedFundingCaseTypes(): array {
        return [];
      }

    };
  }

  public function testCreateTasksOnChangeReworkReviewRequested(): void {
    $previousApplicationProcess = ApplicationProcessFactory::createApplicationProcess([
      'status' => 'previous',
      'is_in_work' => TRUE,
      'is_review_calculative' => TRUE,
      'is_review_content' => TRUE,
    ]);
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle([
      'status' => 'current',
      'is_in_work' => FALSE,
      'is_review_calculative' => TRUE,
      'is_review_content' => TRUE,
      'reviewer_calc_contact_id' => 123,
      'reviewer_cont_contact_id' => 456,
    ]);

    static::assertEquals([
      FundingTaskEntity::newTask([
        'subject' => 'Review Application Rework',
        'affected_identifier' => $applicationProcessBundle->getApplicationProcess()->getIdentifier(),
        'required_permissions' => [
          ApplicationProcessPermissions::REVIEW_CALCULATIVE,
          ApplicationProcessPermissions::REVIEW_CONTENT,
        ],
        'type' => 'application_review_rework',
        'funding_case_id' => $applicationProcessBundle->getFundingCase()->getId(),
        'application_process_id' => $applicationProcessBundle->getApplicationProcess()->getId(),
        'assignee_contact_ids' => [123, 456],
      ]),
    ], [...$this->taskHandler->createTasksOnChange($applicationProcessBundle, $previousApplicationProcess)]);
  }

  public function testCreateTasksOnChangeReviewRequested(): void {
    $previousApplicationProcess = ApplicationProcessFactory::createApplicationProcess([
      'status' => 'previous',
      'is_in_work' => TRUE,
      'is_review_calculative' => NULL,
      'is_review_content' => NULL,
    ]);
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle([
      'status' => 'current',
      'is_in_work' => FALSE,
      'is_review_calculative' => NULL,
      'is_review_content' => NULL,
      'reviewer_calc_contact_id' => 123,
      'reviewer_cont_contact_id' => 456,
    ]);

    static::assertSame(
      [],
      [...$this->taskHandler->createTasksOnChange($applicationProcessBundle, $previousApplicationProcess)]
    );
  }

  public function testCreateTasksOnChangeNotInReworkReviewRequestedStatus(): void {
    $previousApplicationProcess = ApplicationProcessFactory::createApplicationProcess([
      'status' => 'previous',
      'is_in_work' => TRUE,
      'is_review_calculative' => TRUE,
      'is_review_content' => TRUE,
    ]);
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle([
      'status' => 'current',
      'is_in_work' => TRUE,
      'is_review_calculative' => TRUE,
      'is_review_content' => TRUE,
      'reviewer_calc_contact_id' => 123,
      'reviewer_cont_contact_id' => 456,
    ]);

    static::assertSame(
      [],
      [...$this->taskHandler->createTasksOnChange($applicationProcessBundle, $previousApplicationProcess)]
    );
  }

  public function testModifyTaskCancelled(): void {
    $previousApplicationProcess = ApplicationProcessFactory::createApplicationProcess([
      'status' => 'rework-review-requested',
      'is_review_calculative' => TRUE,
      'is_review_content' => TRUE,
    ]);
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle([
      'status' => 'rework-review-requested',
      'is_review_calculative' => TRUE,
      'is_review_content' => NULL,
    ]);

    $task = FundingTaskEntity::newTask([
      'subject' => 'Review Application Rework',
      'affected_identifier' => $previousApplicationProcess->getIdentifier(),
      'required_permissions' => [
        ApplicationProcessPermissions::REVIEW_CALCULATIVE,
        ApplicationProcessPermissions::REVIEW_CONTENT,
      ],
      'type' => 'application_review_rework',
      'funding_case_id' => $previousApplicationProcess->getId(),
      'application_process_id' => $previousApplicationProcess->getId(),
    ]);

    static::assertTrue($this->taskHandler->modifyTask($task, $applicationProcessBundle, $previousApplicationProcess));
    static::assertSame(ActivityStatusNames::CANCELLED, $task->getStatusName());
  }

  public function testModifyTaskCompleted(): void {
    $previousApplicationProcess = ApplicationProcessFactory::createApplicationProcess([
      'status' => 'previous',
      'is_review_calculative' => TRUE,
      'is_review_content' => TRUE,
      'is_in_work' => FALSE,
    ]);
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle([
      'status' => 'now',
      'is_review_calculative' => TRUE,
      'is_review_content' => TRUE,
      'is_in_work' => FALSE,
      'is_eligible' => TRUE,
    ]);

    $task = FundingTaskEntity::newTask([
      'subject' => 'Review Application Rework',
      'affected_identifier' => $previousApplicationProcess->getIdentifier(),
      'required_permissions' => [
        ApplicationProcessPermissions::REVIEW_CALCULATIVE,
        ApplicationProcessPermissions::REVIEW_CONTENT,
      ],
      'type' => 'application_review_rework',
      'funding_case_id' => $previousApplicationProcess->getId(),
      'application_process_id' => $previousApplicationProcess->getId(),
    ]);

    static::assertTrue($this->taskHandler->modifyTask($task, $applicationProcessBundle, $previousApplicationProcess));
    static::assertSame(ActivityStatusNames::COMPLETED, $task->getStatusName());
  }

  public function testModifyTaskBackInRework(): void {
    $previousApplicationProcess = ApplicationProcessFactory::createApplicationProcess([
      'status' => 'previous',
      'is_review_calculative' => TRUE,
      'is_review_content' => TRUE,
      'is_in_work' => FALSE,
    ]);
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle([
      'status' => 'current',
      'is_review_calculative' => TRUE,
      'is_review_content' => TRUE,
      'is_in_work' => TRUE,
    ]);

    $task = FundingTaskEntity::newTask([
      'subject' => 'Review Application Rework',
      'affected_identifier' => $previousApplicationProcess->getIdentifier(),
      'required_permissions' => [
        ApplicationProcessPermissions::REVIEW_CALCULATIVE,
        ApplicationProcessPermissions::REVIEW_CONTENT,
      ],
      'type' => 'application_review_rework',
      'funding_case_id' => $previousApplicationProcess->getId(),
      'application_process_id' => $previousApplicationProcess->getId(),
    ]);

    static::assertTrue($this->taskHandler->modifyTask($task, $applicationProcessBundle, $previousApplicationProcess));
    static::assertSame(ActivityStatusNames::COMPLETED, $task->getStatusName());
  }

  public function testModifyTaskAssigneeChanged(): void {
    $previousApplicationProcess = ApplicationProcessFactory::createApplicationProcess([
      'status' => 'test',
      'is_in_work' => FALSE,
      'is_review_calculative' => TRUE,
      'is_review_content' => TRUE,
      'reviewer_calc_contact_id' => 123,
      'reviewer_cont_contact_id' => NULL,
    ]);
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle([
      'status' => 'test',
      'is_in_work' => FALSE,
      'is_review_calculative' => TRUE,
      'is_review_content' => TRUE,
      'reviewer_calc_contact_id' => NULL,
      'reviewer_cont_contact_id' => 456,
    ]);

    $task = FundingTaskEntity::newTask([
      'subject' => 'Review Application Rework',
      'affected_identifier' => $previousApplicationProcess->getIdentifier(),
      'required_permissions' => [
        ApplicationProcessPermissions::REVIEW_CALCULATIVE,
        ApplicationProcessPermissions::REVIEW_CONTENT,
      ],
      'type' => 'application_review_rework',
      'funding_case_id' => $previousApplicationProcess->getId(),
      'application_process_id' => $previousApplicationProcess->getId(),
    ]);

    static::assertTrue($this->taskHandler->modifyTask($task, $applicationProcessBundle, $previousApplicationProcess));
    static::assertSame(ActivityStatusNames::SCHEDULED, $task->getStatusName());
    static::assertSame([456], $task->get('assignee_contact_id'));
  }

  public function testModifyTaskDifferentTaskType(): void {
    $previousApplicationProcess = ApplicationProcessFactory::createApplicationProcess([
      'status' => 'rework',
      'is_review_calculative' => TRUE,
      'is_review_content' => TRUE,
    ]);
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle([
      'status' => 'rework-review-requested',
      'is_review_calculative' => TRUE,
      'is_review_content' => TRUE,
    ]);

    $task = FundingTaskEntity::newTask([
      'subject' => 'Some Task',
      'affected_identifier' => $previousApplicationProcess->getIdentifier(),
      'required_permissions' => [
        ApplicationProcessPermissions::REVIEW_CALCULATIVE,
        ApplicationProcessPermissions::REVIEW_CONTENT,
      ],
      'type' => 'some_type',
      'funding_case_id' => $previousApplicationProcess->getId(),
      'application_process_id' => $previousApplicationProcess->getId(),
    ]);

    static::assertFalse($this->taskHandler->modifyTask($task, $applicationProcessBundle, $previousApplicationProcess));
    static::assertSame(ActivityStatusNames::SCHEDULED, $task->getStatusName());
  }

}
