<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
 *
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License as published by the Free
 * Software Foundation, either version 3 of the License, or (at your option) any
 * later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types = 1);

namespace Civi\Funding\ApplicationProcess\Task;

use Civi\Funding\ActivityStatusNames;
use Civi\Funding\Entity\FundingTaskEntity;
use Civi\Funding\EntityFactory\ApplicationProcessBundleFactory;
use Civi\Funding\EntityFactory\ApplicationProcessFactory;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\ApplicationProcess\Task\AbstractApplicationReviewReworkRequestTaskHandler
 */
final class AbstractApplicationReviewReworkRequestTaskHandlerTest extends TestCase {

  private AbstractApplicationReviewReworkRequestTaskHandler $taskHandler;

  protected function setUp(): void {
    parent::setUp();
    $this->taskHandler = new class ()
      extends AbstractApplicationReviewReworkRequestTaskHandler {

      public static function getSupportedFundingCaseTypes(): array {
        return [];
      }

    };
  }

  public function testCreateTasksOnChangeReworkRequested(): void {
    $previousApplication = ApplicationProcessFactory::createApplicationProcess(['status' => 'old']);
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle([
      'status' => 'rework-requested',
    ]);

    $tasks = [...$this->taskHandler->createTasksOnChange($applicationProcessBundle, $previousApplication)];
    static::assertEquals([
      FundingTaskEntity::newTask([
        'subject' => 'Review Rework Request',
        'affected_identifier' => $applicationProcessBundle->getApplicationProcess()->getIdentifier(),
        'required_permissions' => ['review_calculative', 'review_content'],
        'type' => 'application_review_rework_request',
        'funding_case_id' => $applicationProcessBundle->getFundingCase()->getId(),
        'application_process_id' => $applicationProcessBundle->getApplicationProcess()->getId(),
      ]),
    ], $tasks);
  }

  public function testCreateTasksOnChangeNotReworkRequested(): void {
    $previousApplication = ApplicationProcessFactory::createApplicationProcess(['status' => 'old']);
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle([
      'status' => 'new',
    ]);

    $tasks = [...$this->taskHandler->createTasksOnChange($applicationProcessBundle, $previousApplication)];
    static::assertSame([], $tasks);
  }

  public function testCreateTasksOnNew(): void {
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle(['status' => 'test']);

    $tasks = [...$this->taskHandler->createTasksOnNew($applicationProcessBundle)];
    static::assertSame([], $tasks);
  }

  public function testModifyTaskNotReworkRequested(): void {
    $previousApplicationProcess = ApplicationProcessFactory::createApplicationProcess(['status' => 'rework-requested']);
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle([
      'status' => 'rework',
    ]);

    $task = FundingTaskEntity::newTask([
      'subject' => 'Review Rework Request',
      'affected_identifier' => $applicationProcessBundle->getApplicationProcess()->getIdentifier(),
      'required_permissions' => ['review_calculative', 'review_content'],
      'type' => 'application_review_rework_request',
      'funding_case_id' => $applicationProcessBundle->getFundingCase()->getId(),
      'application_process_id' => $applicationProcessBundle->getApplicationProcess()->getId(),
    ]);

    static::assertTrue($this->taskHandler->modifyTask($task, $applicationProcessBundle, $previousApplicationProcess));
    static::assertSame(ActivityStatusNames::COMPLETED, $task->getStatusName());
  }

  public function testModifyTaskStillReworkRequested(): void {
    $previousApplicationProcess = ApplicationProcessFactory::createApplicationProcess(['status' => 'rework-requested']);
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle([
      'status' => 'rework-requested',
    ]);

    $task = FundingTaskEntity::newTask([
      'subject' => 'Review Rework Request',
      'affected_identifier' => $applicationProcessBundle->getApplicationProcess()->getIdentifier(),
      'required_permissions' => ['review_calculative', 'review_content'],
      'type' => 'application_review_rework_request',
      'funding_case_id' => $applicationProcessBundle->getFundingCase()->getId(),
      'application_process_id' => $applicationProcessBundle->getApplicationProcess()->getId(),
    ]);

    static::assertFalse($this->taskHandler->modifyTask($task, $applicationProcessBundle, $previousApplicationProcess));
    static::assertSame(ActivityStatusNames::SCHEDULED, $task->getStatusName());
  }

  public function testModifyTaskDifferentTaskType(): void {
    $previousApplicationProcess = ApplicationProcessFactory::createApplicationProcess(['status' => 'rework-requested']);
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle([
      'status' => 'rework',
    ]);

    $task = FundingTaskEntity::newTask([
      'subject' => 'Some Task',
      'affected_identifier' => $applicationProcessBundle->getApplicationProcess()->getIdentifier(),
      'required_permissions' => ['review_calculative', 'review_content'],
      'type' => 'some_type',
      'funding_case_id' => $applicationProcessBundle->getFundingCase()->getId(),
      'application_process_id' => $applicationProcessBundle->getApplicationProcess()->getId(),
    ]);

    static::assertFalse($this->taskHandler->modifyTask($task, $applicationProcessBundle, $previousApplicationProcess));
    static::assertSame(ActivityStatusNames::SCHEDULED, $task->getStatusName());
  }

}
