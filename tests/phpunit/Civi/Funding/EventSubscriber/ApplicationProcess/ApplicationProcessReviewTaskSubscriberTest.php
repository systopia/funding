<?php
/*
 * Copyright (C) 2023 SYSTOPIA GmbH
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

namespace Civi\Funding\EventSubscriber\ApplicationProcess;

use Civi\Funding\ActivityTypeIds;
use Civi\Funding\ApplicationProcess\ActionStatusInfo\ApplicationProcessActionStatusInfoContainer;
use Civi\Funding\ApplicationProcess\ActionStatusInfo\DefaultApplicationProcessActionStatusInfo;
use Civi\Funding\ApplicationProcess\ApplicationProcessTaskManager;
use Civi\Funding\ApplicationProcess\TaskType;
use Civi\Funding\Entity\ActivityEntity;
use Civi\Funding\EntityFactory\ApplicationProcessBundleFactory;
use Civi\Funding\EntityFactory\ApplicationProcessFactory;
use Civi\Funding\EntityFactory\FundingCaseTypeFactory;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessUpdatedEvent;
use Civi\Funding\Mock\Psr\PsrContainer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\EventSubscriber\ApplicationProcess\ApplicationProcessReviewTaskSubscriber
 */
final class ApplicationProcessReviewTaskSubscriberTest extends TestCase {

  private ApplicationProcessReviewTaskSubscriber $subscriber;

  /**
   * @var \Civi\Funding\ApplicationProcess\ApplicationProcessTaskManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $taskManagerMock;

  protected function setUp(): void {
    parent::setUp();
    $infoContainer = new ApplicationProcessActionStatusInfoContainer(new PsrContainer([
      FundingCaseTypeFactory::DEFAULT_NAME => new DefaultApplicationProcessActionStatusInfo(),
    ]));
    $this->taskManagerMock = $this->createMock(ApplicationProcessTaskManager::class);
    $this->subscriber = new ApplicationProcessReviewTaskSubscriber(
      $infoContainer,
      $this->taskManagerMock,
    );
  }

  public function testGetSubscribedEvents(): void {
    $expectedSubscriptions = [
      ApplicationProcessUpdatedEvent::class => ['onUpdated'],
    ];

    static::assertEquals($expectedSubscriptions, $this->subscriber::getSubscribedEvents());

    foreach ($expectedSubscriptions as [$method]) {
      static::assertTrue(method_exists(get_class($this->subscriber), $method));
    }
  }

  public function testOnUpdatedReviewStatus(): void {
    $event = $this->createUpdatedEvent(['status' => ['applied', 'review']]);
    $applicationProcess = $event->getApplicationProcess();
    $applicationProcess
      ->setReviewerCalculativeContactId(2)
      ->setReviewerContentContactId(3);
    $event->getPreviousApplicationProcess()
      ->setReviewerCalculativeContactId(2)
      ->setReviewerContentContactId(3);

    $task1 = ActivityEntity::fromArray([
      'activity_type_id' => ActivityTypeIds::FUNDING_APPLICATION_TASK_INTERNAL,
      'subject' => 'Subject',
    ]);
    $task2 = ActivityEntity::fromArray([
      'activity_type_id' => ActivityTypeIds::FUNDING_APPLICATION_TASK_INTERNAL,
      'subject' => 'Subject',
    ]);

    $this->taskManagerMock->expects(static::exactly(2))->method('addOrAssignInternalTask')
      ->withConsecutive(
        [1, $applicationProcess, 2, TaskType::REVIEW_CALCULATIVE, 'Review Funding Application (calculative)'],
        [1, $applicationProcess, 3, TaskType::REVIEW_CONTENT, 'Review Funding Application (content)'],
      )->willReturnOnConsecutiveCalls($task1, $task2);
    $this->subscriber->onUpdated($event);
  }

  public function testOnUpdatedReviewerCalculativeContactId(): void {
    $event = $this->createUpdatedEvent(['reviewer_calc_contact_id' => [2, 3]]);
    $this->taskManagerMock->expects(static::once())->method('assignInternalTask')
      ->with($event->getApplicationProcess()->getId(), 3, TaskType::REVIEW_CALCULATIVE);

    $this->subscriber->onUpdated($event);
  }

  public function testOnUpdatedReviewerContentContactId(): void {
    $event = $this->createUpdatedEvent(['reviewer_cont_contact_id' => [2, 3]]);
    $this->taskManagerMock->expects(static::once())->method('assignInternalTask')
      ->with($event->getApplicationProcess()->getId(), 3, TaskType::REVIEW_CONTENT);

    $this->subscriber->onUpdated($event);
  }

  public function testOnUpdatedReviewCalculativeResult(): void {
    $event = $this->createUpdatedEvent(['is_review_calculative' => [NULL, TRUE]]);
    $this->taskManagerMock->expects(static::once())->method('completeInternalTask')
      ->with($event->getApplicationProcess()->getId(), TaskType::REVIEW_CALCULATIVE);

    $this->subscriber->onUpdated($event);
  }

  public function testOnUpdatedReviewCalculativeResultReset(): void {
    $event = $this->createUpdatedEvent(['is_review_calculative' => [TRUE, NULL]]);
    $this->taskManagerMock->expects(static::never())->method('completeInternalTask');

    $this->subscriber->onUpdated($event);
  }

  public function testOnUpdatedReviewContentResult(): void {
    $event = $this->createUpdatedEvent(['is_review_content' => [NULL, TRUE]]);
    $this->taskManagerMock->expects(static::once())->method('completeInternalTask')
      ->with($event->getApplicationProcess()->getId(), TaskType::REVIEW_CONTENT);

    $this->subscriber->onUpdated($event);
  }

  public function testOnUpdatedReviewContentResultReset(): void {
    $event = $this->createUpdatedEvent(['is_review_calculative' => [TRUE, NULL]]);
    $this->taskManagerMock->expects(static::never())->method('completeInternalTask');

    $this->subscriber->onUpdated($event);
  }

  public function testOnUpdatedNoReviewStatus(): void {
    $event = $this->createUpdatedEvent(['status' => ['review', 'foo']]);
    $this->taskManagerMock->expects(static::exactly(2))->method('cancelInternalTask')->withConsecutive(
      [$event->getApplicationProcess()->getId(), TaskType::REVIEW_CALCULATIVE],
      [$event->getApplicationProcess()->getId(), TaskType::REVIEW_CONTENT],
    );

    $this->subscriber->onUpdated($event);
  }

  /**
   * @phpstan-param array<string, array<mixed, mixed>> $changeSet
   */
  private function createUpdatedEvent(array $changeSet): ApplicationProcessUpdatedEvent {
    $previousValues = array_map(fn(array $oldAndNew) => $oldAndNew[0], $changeSet);
    $currentValues = array_map(fn(array $oldAndNew) => $oldAndNew[1], $changeSet);

    // @phpstan-ignore-next-line
    $previousApplicationProcess = ApplicationProcessFactory::createApplicationProcess($previousValues);
    // @phpstan-ignore-next-line
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle($currentValues);

    return new ApplicationProcessUpdatedEvent(
      1,
      $previousApplicationProcess,
      $applicationProcessBundle,
    );
  }

}
