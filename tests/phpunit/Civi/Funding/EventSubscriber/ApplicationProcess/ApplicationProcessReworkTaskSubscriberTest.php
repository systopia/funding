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
use Civi\Funding\ApplicationProcess\Command\ApplicationFormSubmitResult;
use Civi\Funding\ApplicationProcess\TaskType;
use Civi\Funding\Entity\ActivityEntity;
use Civi\Funding\EntityFactory\ApplicationProcessBundleFactory;
use Civi\Funding\EntityFactory\ApplicationProcessFactory;
use Civi\Funding\EntityFactory\FundingCaseTypeFactory;
use Civi\Funding\Event\ApplicationProcess\ApplicationFormSubmitSuccessEvent;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessUpdatedEvent;
use Civi\Funding\Form\Application\ApplicationValidationResult;
use Civi\Funding\Mock\Form\ValidatedApplicationDataMock;
use Civi\Funding\Mock\Psr\PsrContainer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\EventSubscriber\ApplicationProcess\ApplicationProcessReworkTaskSubscriber
 */
final class ApplicationProcessReworkTaskSubscriberTest extends TestCase {

  private ApplicationProcessReworkTaskSubscriber $subscriber;

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
    $this->subscriber = new ApplicationProcessReworkTaskSubscriber(
      $infoContainer,
      $this->taskManagerMock,
    );
  }

  public function testGetSubscribedEvents(): void {
    $expectedSubscriptions = [
      ApplicationFormSubmitSuccessEvent::class => 'onFormSubmitSuccess',
      ApplicationProcessUpdatedEvent::class => 'onUpdated',
    ];

    static::assertEquals($expectedSubscriptions, $this->subscriber::getSubscribedEvents());

    foreach ($expectedSubscriptions as $method) {
      static::assertTrue(method_exists(get_class($this->subscriber), $method));
    }
  }

  public function testOnFormSubmitSuccessApply(): void {
    $event = new ApplicationFormSubmitSuccessEvent(
      1,
      ApplicationProcessBundleFactory::createApplicationProcessBundle(),
      [],
      ApplicationFormSubmitResult::createSuccess(
        ApplicationValidationResult::newValid(new ValidatedApplicationDataMock([], ['_action' => 'apply']), FALSE)
      ),
    );
    $applicationProcess = $event->getApplicationProcess();

    $this->taskManagerMock->expects(static::once())->method('completeExternalTask')
      ->with($applicationProcess->getId(), TaskType::REWORK);
    $this->subscriber->onFormSubmitSuccess($event);
  }

  public function testOnFormSubmitSuccessNoChangeRequiredStatus(): void {
    $event = new ApplicationFormSubmitSuccessEvent(
      1,
      ApplicationProcessBundleFactory::createApplicationProcessBundle(['status' => 'foo']),
      [],
      ApplicationFormSubmitResult::createSuccess(
        ApplicationValidationResult::newValid(new ValidatedApplicationDataMock([], ['_action' => 'some-action']), FALSE)
      ),
    );
    $applicationProcess = $event->getApplicationProcess();

    $this->taskManagerMock->expects(static::once())->method('cancelExternalTask')
      ->with($applicationProcess->getId(), TaskType::REWORK);
    $this->subscriber->onFormSubmitSuccess($event);
  }

  public function testOnFormSubmitSuccessChangeRequiredStatus(): void {
    $event = new ApplicationFormSubmitSuccessEvent(
      1,
      ApplicationProcessBundleFactory::createApplicationProcessBundle(['status' => 'draft']),
      [],
      ApplicationFormSubmitResult::createSuccess(
        ApplicationValidationResult::newValid(new ValidatedApplicationDataMock([], ['_action' => 'some-action']), FALSE)
      ),
    );

    $this->taskManagerMock->expects(static::never())->method('cancelExternalTask');
    $this->subscriber->onFormSubmitSuccess($event);
  }

  public function testOnUpdatedChangeRequiredStatus(): void {
    $event = $this->createUpdatedEvent(['status' => ['some-status', 'draft']]);

    $task = ActivityEntity::fromArray([
      'activity_type_id' => ActivityTypeIds::FUNDING_APPLICATION_TASK_EXTERNAL,
      'subject' => 'Subject',
    ]);

    $this->taskManagerMock->expects(static::once())->method('addExternalTask')
      ->with($event->getContactId(), $event->getApplicationProcess(), TaskType::REWORK, 'Rework Funding Application')
      ->willReturn($task);
    $this->subscriber->onUpdated($event);
  }

  public function testOnUpdatedNoChangeRequiredStatus(): void {
    $event = $this->createUpdatedEvent(['status' => ['old-status', 'new-status']]);

    $this->taskManagerMock->expects(static::never())->method('addExternalTask');
    $this->subscriber->onUpdated($event);
  }

  /**
   * @phpstan-param array<string, array<mixed, mixed>> $changeSet
   */
  private function createUpdatedEvent(array $changeSet): ApplicationProcessUpdatedEvent {
    $previousValues = array_map(fn(array $oldAndNew) => $oldAndNew[0], $changeSet);
    $currentValues = array_map(fn(array $oldAndNew) => $oldAndNew[1], $changeSet);

    $previousApplicationProcess = ApplicationProcessFactory::createApplicationProcess($previousValues);
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle($currentValues);

    return new ApplicationProcessUpdatedEvent(
      1,
      $previousApplicationProcess,
      $applicationProcessBundle,
    );
  }

}
