<?php
/*
 * Copyright (C) 2022 SYSTOPIA GmbH
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

use Civi\Api4\FundingApplicationProcess;
use Civi\Funding\ActivityTypeIds;
use Civi\Funding\ApplicationProcess\ApplicationProcessActivityManager;
use Civi\Funding\Entity\ActivityEntity;
use Civi\Funding\EntityFactory\ApplicationProcessFactory;
use Civi\Funding\EntityFactory\FundingCaseFactory;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessUpdatedEvent;
use Civi\RemoteTools\Api4\OptionsLoaderInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\EventSubscriber\ApplicationProcess\ApplicationProcessStatusSubscriber
 */
final class ApplicationProcessStatusSubscriberTest extends TestCase {

  /**
   * @var \Civi\Funding\ApplicationProcess\ApplicationProcessActivityManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $activityManagerMock;

  /**
   * @var \Civi\RemoteTools\Api4\OptionsLoaderInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $optionsLoaderMock;

  private ApplicationProcessStatusSubscriber $subscriber;

  protected function setUp(): void {
    parent::setUp();
    $this->activityManagerMock = $this->createMock(ApplicationProcessActivityManager::class);
    $this->optionsLoaderMock = $this->createMock(OptionsLoaderInterface::class);
    $this->subscriber = new ApplicationProcessStatusSubscriber(
      $this->activityManagerMock,
      $this->optionsLoaderMock
    );
  }

  public function testGetSubscribedEvents(): void {
    $expectedSubscriptions = [
      ApplicationProcessUpdatedEvent::class => 'onUpdated',
    ];

    static::assertEquals($expectedSubscriptions, $this->subscriber::getSubscribedEvents());

    foreach ($expectedSubscriptions as $method) {
      static::assertTrue(method_exists(get_class($this->subscriber), $method));
    }
  }

  public function testOnUpdated(): void {
    $event = $this->createEvent('old-status', 'new-status');
    $activity = ActivityEntity::fromArray([
      'activity_type_id' => ActivityTypeIds::FUNDING_APPLICATION_STATUS_CHANGE,
      'subject' => 'Funding application process status changed',
      'details'
      => '<ul><li>Application process: Title (Identifier)</li><li>Old status: Old</li><li>New status: New</li></ul>',
      'funding_application_status_change.old_status' => 'old-status',
      'funding_application_status_change.new_status' => 'new-status',
    ]);

    $this->optionsLoaderMock->expects(static::exactly(2))->method('getOptionLabel')
      ->withConsecutive(
        [FundingApplicationProcess::_getEntityName(), 'status', 'old-status'],
        [FundingApplicationProcess::_getEntityName(), 'status', 'new-status'],
      )->willReturnOnConsecutiveCalls('Old', 'New');
    $this->activityManagerMock->expects(static::once())->method('addActivity')
      ->with($event->getContactId(), $event->getApplicationProcess(), $activity);

    $this->subscriber->onUpdated($event);
  }

  public function testOnUpdatedNoStatusChange(): void {
    $event = $this->createEvent('status', 'status');

    $this->optionsLoaderMock->expects(static::never())->method('getOptionLabel');
    $this->activityManagerMock->expects(static::never())->method('addActivity');

    $this->subscriber->onUpdated($event);
  }

  private function createEvent(string $oldStatus, string $newStatus): ApplicationProcessUpdatedEvent {
    $applicationProcessValues = [
      'title' => 'Title',
      'identifier' => 'Identifier',
    ];

    return new ApplicationProcessUpdatedEvent(
      11,
      ApplicationProcessFactory::createApplicationProcess($applicationProcessValues + ['status' => $oldStatus]),
      ApplicationProcessFactory::createApplicationProcess($applicationProcessValues + ['status' => $newStatus]),
      FundingCaseFactory::createFundingCase(),
    );
  }

}
