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
use Civi\Funding\ApplicationProcess\ApplicationProcessActivityManager;
use Civi\Funding\Entity\ActivityEntity;
use Civi\Funding\EntityFactory\ApplicationProcessBundleFactory;
use Civi\Funding\EntityFactory\ApplicationProcessFactory;
use Civi\Funding\EntityFactory\ApplicationSnapshotFactory;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessUpdatedEvent;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\EventSubscriber\ApplicationProcess\ApplicationSnapshotRestoreSubscriber
 */
final class ApplicationSnapshotRestoreSubscriberTest extends TestCase {

  /**
   * @var \Civi\Funding\ApplicationProcess\ApplicationProcessActivityManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $activityManagerMock;

  private ApplicationSnapshotRestoreSubscriber $subscriber;

  protected function setUp(): void {
    parent::setUp();
    $this->activityManagerMock = $this->createMock(ApplicationProcessActivityManager::class);
    $this->subscriber = new ApplicationSnapshotRestoreSubscriber($this->activityManagerMock);
  }

  public function testGetSubscribedEvents(): void {
    $expectedSubscriptions = [
      ApplicationProcessUpdatedEvent::class => ['onUpdated', PHP_INT_MIN],
    ];

    static::assertEquals($expectedSubscriptions, $this->subscriber::getSubscribedEvents());

    foreach ($expectedSubscriptions as [$method]) {
      static::assertTrue(method_exists(get_class($this->subscriber), $method));
    }
  }

  public function testOnUpdated(): void {
    $event = $this->createUpdatedEvent([
      'title' => 'Test Title',
      'identifier' => 'Identifier',
    ]);
    $applicationSnapshot = ApplicationSnapshotFactory::createApplicationSnapshot([
      'creation_date' => '2023-02-10 10:10:10',
    ]);
    $event->getApplicationProcess()->setRestoredSnapshot($applicationSnapshot);

    $this->activityManagerMock->expects(static::once())->method('addActivity')
      ->with(
        $event->getContactId(),
        $event->getApplicationProcess(),
        static::callback(function (ActivityEntity $activity) {
          static::assertSame(ActivityTypeIds::FUNDING_APPLICATION_RESTORE, $activity->getActivityTypeId());
          static::assertSame(
            ApplicationSnapshotFactory::DEFAULT_ID,
            $activity->get('funding_application_restore.application_snapshot_id')
          );
          static::assertSame('Funding application process restored', $activity->getSubject());
          static::assertSame(
            'Application process "Test Title" (Identifier) restored to version from 2023-02-10 10:10:10.',
            $activity->getDetails()
          );

          return TRUE;
        })
      );
    $this->subscriber->onUpdated($event);
  }

  public function testOnUpdatedNoRestore(): void {
    $event = $this->createUpdatedEvent();

    $this->activityManagerMock->expects(static::never())->method('addActivity');
    $this->subscriber->onUpdated($event);
  }

  /**
   * @phpstan-param array<string, mixed> $currentValues
   */
  private function createUpdatedEvent(array $currentValues = []): ApplicationProcessUpdatedEvent {
    $previousApplicationProcess = ApplicationProcessFactory::createApplicationProcess();
    // @phpstan-ignore-next-line
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle($currentValues);

    return new ApplicationProcessUpdatedEvent(
      1,
      $previousApplicationProcess,
      $applicationProcessBundle,
    );
  }

}
