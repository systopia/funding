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
 * @covers \Civi\Funding\ApplicationProcess\Task\AbstractApplicationReviewFinishTaskHandler
 */
final class AbstractApplicationReviewFinishTaskHandlerTest extends TestCase {

  /**
   * @var \Civi\Funding\ApplicationProcess\ActionStatusInfo\ApplicationProcessActionStatusInfoInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $infoMock;

  private AbstractApplicationReviewFinishTaskHandler $taskHandler;

  protected function setUp(): void {
    parent::setUp();
    $this->infoMock = $this->createMock(ApplicationProcessActionStatusInfoInterface::class);
    $infoContainer = new ApplicationProcessActionStatusInfoContainer(new PsrContainer([
      FundingCaseTypeFactory::DEFAULT_NAME => $this->infoMock,
    ]));
    $this->taskHandler = $this->getMockForAbstractClass(
      AbstractApplicationReviewFinishTaskHandler::class,
      [$infoContainer]
    );
  }

  public function testCreateTasksOnChangeCalculativeAndContentReviewDone(): void {
    $previousApplicationProcess = ApplicationProcessFactory::createApplicationProcess([
      'status' => 'review',
      'is_review_calculative' => TRUE,
      'is_review_content' => NULL,
    ]);
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle([
      'status' => 'review',
      'is_review_calculative' => TRUE,
      'is_review_content' => FALSE,
      'reviewer_calc_contact_id' => 123,
      'reviewer_cont_contact_id' => 456,
    ]);

    $this->infoMock->expects(static::once())->method('isReviewStatus')
      ->with('review')
      ->willReturn(TRUE);

    static::assertEquals([
      FundingTaskEntity::newTask([
        'subject' => 'Finish Application Review',
        'affected_identifier' => $applicationProcessBundle->getApplicationProcess()->getIdentifier(),
        'required_permissions' => [
          ApplicationProcessPermissions::REVIEW_CALCULATIVE,
          ApplicationProcessPermissions::REVIEW_CONTENT,
        ],
        'type' => 'review_finish',
        'funding_case_id' => $applicationProcessBundle->getFundingCase()->getId(),
        'application_process_id' => $applicationProcessBundle->getApplicationProcess()->getId(),
        'assignee_contact_ids' => [123, 456],
      ]),
    ], [...$this->taskHandler->createTasksOnChange($applicationProcessBundle, $previousApplicationProcess)]);
  }

  public function testCreateTasksOnChangeNotInReviewStatus(): void {
    $previousApplicationProcess = ApplicationProcessFactory::createApplicationProcess([
      'status' => 'test',
      'is_review_calculative' => TRUE,
      'is_review_content' => NULL,
    ]);
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle([
      'status' => 'test',
      'is_review_calculative' => TRUE,
      'is_review_content' => FALSE,
      'reviewer_calc_contact_id' => 123,
      'reviewer_cont_contact_id' => 456,
    ]);

    $this->infoMock->expects(static::once())->method('isReviewStatus')
      ->with('test')
      ->willReturn(FALSE);

    static::assertSame(
      [],
      [...$this->taskHandler->createTasksOnChange($applicationProcessBundle, $previousApplicationProcess)]
    );
  }

  public function testModifyTaskCancelled(): void {
    $previousApplicationProcess = ApplicationProcessFactory::createApplicationProcess([
      'status' => 'review',
      'is_review_calculative' => TRUE,
      'is_review_content' => TRUE,
    ]);
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle([
      'status' => 'review',
      'is_review_calculative' => TRUE,
      'is_review_content' => NULL,
    ]);

    $task = FundingTaskEntity::newTask([
      'subject' => 'Finish Application Review',
      'affected_identifier' => $previousApplicationProcess->getIdentifier(),
      'required_permissions' => [
        ApplicationProcessPermissions::REVIEW_CALCULATIVE,
        ApplicationProcessPermissions::REVIEW_CONTENT,
      ],
      'type' => 'review_finish',
      'funding_case_id' => $previousApplicationProcess->getId(),
      'application_process_id' => $previousApplicationProcess->getId(),
    ]);

    $this->infoMock->expects(static::once())->method('isReviewStatus')
      ->with('review')
      ->willReturn(TRUE);

    static::assertTrue($this->taskHandler->modifyTask($task, $applicationProcessBundle, $previousApplicationProcess));
    static::assertSame(ActivityStatusNames::CANCELLED, $task->getStatusName());
  }

  public function testModifyTaskCompleted(): void {
    $previousApplicationProcess = ApplicationProcessFactory::createApplicationProcess(['status' => 'review']);
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle(
      ['status' => 'rejected']
    );

    $task = FundingTaskEntity::newTask([
      'subject' => 'Finish Application Review',
      'affected_identifier' => $previousApplicationProcess->getIdentifier(),
      'required_permissions' => [
        ApplicationProcessPermissions::REVIEW_CALCULATIVE,
        ApplicationProcessPermissions::REVIEW_CONTENT,
      ],
      'type' => 'review_finish',
      'funding_case_id' => $previousApplicationProcess->getId(),
      'application_process_id' => $previousApplicationProcess->getId(),
    ]);

    $this->infoMock->expects(static::once())->method('isReviewStatus')
      ->with('rejected')
      ->willReturn(FALSE);

    static::assertTrue($this->taskHandler->modifyTask($task, $applicationProcessBundle, $previousApplicationProcess));
    static::assertSame(ActivityStatusNames::COMPLETED, $task->getStatusName());
  }

  public function testModifyTaskAssigneeChanged(): void {
    $previousApplicationProcess = ApplicationProcessFactory::createApplicationProcess([
      'status' => 'review',
      'is_review_calculative' => TRUE,
      'is_review_content' => TRUE,
      'reviewer_calc_contact_id' => 123,
      'reviewer_cont_contact_id' => NULL,
    ]);
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle([
      'status' => 'review',
      'is_review_calculative' => TRUE,
      'is_review_content' => TRUE,
      'reviewer_calc_contact_id' => NULL,
      'reviewer_cont_contact_id' => 456,
    ]);

    $task = FundingTaskEntity::newTask([
      'subject' => 'Finish Application Review',
      'affected_identifier' => $previousApplicationProcess->getIdentifier(),
      'required_permissions' => [
        ApplicationProcessPermissions::REVIEW_CALCULATIVE,
        ApplicationProcessPermissions::REVIEW_CONTENT,
      ],
      'type' => 'review_finish',
      'funding_case_id' => $previousApplicationProcess->getId(),
      'application_process_id' => $previousApplicationProcess->getId(),
    ]);

    $this->infoMock->expects(static::once())->method('isReviewStatus')
      ->with('review')
      ->willReturn(TRUE);

    static::assertTrue($this->taskHandler->modifyTask($task, $applicationProcessBundle, $previousApplicationProcess));
    static::assertSame(ActivityStatusNames::SCHEDULED, $task->getStatusName());
    static::assertSame([456], $task->get('assignee_contact_id'));
  }

  public function testModifyTaskDifferentTaskType(): void {
    $previousApplicationProcess = ApplicationProcessFactory::createApplicationProcess([
      'status' => 'review',
      'is_review_calculative' => TRUE,
      'is_review_content' => FALSE,
    ]);
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle([
      'status' => 'rejected',
      'is_review_calculative' => TRUE,
      'is_review_content' => FALSE,
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

    $this->infoMock->expects(static::never())->method('isReviewStatus');

    static::assertFalse($this->taskHandler->modifyTask($task, $applicationProcessBundle, $previousApplicationProcess));
    static::assertSame(ActivityStatusNames::SCHEDULED, $task->getStatusName());
  }

}
