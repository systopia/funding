<?php
/*
 * Copyright (C) 2024 SYSTOPIA GmbH
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

namespace Civi\Funding\EventSubscriber\ClearingProcess;

use Civi\Api4\FundingClearingProcess;
use Civi\Funding\ActivityTypeNames;
use Civi\Funding\ApplicationProcess\ApplicationProcessActivityManager;
use Civi\Funding\Entity\ActivityEntity;
use Civi\Funding\EntityFactory\ClearingProcessBundleFactory;
use Civi\Funding\EntityFactory\ClearingProcessFactory;
use Civi\Funding\Event\ClearingProcess\ClearingProcessUpdatedEvent;
use Civi\RemoteTools\Api4\OptionsLoaderInterface;
use Civi\RemoteTools\RequestContext\RequestContextInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\EventSubscriber\ClearingProcess\ClearingProcessStatusSubscriber
 */
final class ClearingProcessStatusSubscriberTest extends TestCase {

  /**
   * @var \Civi\Funding\ApplicationProcess\ApplicationProcessActivityManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $activityManagerMock;

  /**
   * @var \Civi\RemoteTools\Api4\OptionsLoaderInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $optionsLoaderMock;

  private ClearingProcessStatusSubscriber $subscriber;

  protected function setUp(): void {
    parent::setUp();
    $this->activityManagerMock = $this->createMock(ApplicationProcessActivityManager::class);
    $this->optionsLoaderMock = $this->createMock(OptionsLoaderInterface::class);
    $requestContextMock = $this->createMock(RequestContextInterface::class);
    $requestContextMock->method('getContactId')->willReturn(22);
    $this->subscriber = new ClearingProcessStatusSubscriber(
      $this->activityManagerMock,
      $this->optionsLoaderMock,
      $requestContextMock
    );
  }

  public function testGetSubscribedEvents(): void {
    $expectedSubscriptions = [
      ClearingProcessUpdatedEvent::class => 'onUpdated',
    ];

    static::assertEquals($expectedSubscriptions, $this->subscriber::getSubscribedEvents());

    foreach ($expectedSubscriptions as $method) {
      static::assertTrue(method_exists(get_class($this->subscriber), $method));
    }
  }

  public function testOnUpdated(): void {
    $event = $this->createEvent('old-status', 'new-status');
    $activity = ActivityEntity::fromArray([
      'activity_type_id:name' => ActivityTypeNames::FUNDING_CLEARING_STATUS_CHANGE,
      'subject' => 'Funding Clearing Status Changed',
      'details'
      => '<ul><li>Application: Title (Identifier)</li><li>From status: Old</li><li>To status: New</li></ul>',
      'funding_clearing_status_change.from_status' => 'old-status',
      'funding_clearing_status_change.to_status' => 'new-status',
    ]);

    $series = [
      [
        [FundingClearingProcess::getEntityName(), 'status', 'old-status'],
        'Old',
      ],
      [
        [FundingClearingProcess::getEntityName(), 'status', 'new-status'],
        'New',
      ],
    ];
    $this->optionsLoaderMock->expects(static::exactly(2))->method('getOptionLabel')
      ->willReturnCallback(function (...$args) use (&$series) {
        // @phpstan-ignore-next-line
        [$expectedArgs, $return] = array_shift($series);
        static::assertEquals($expectedArgs, $args);

        return $return;
      });

    $this->activityManagerMock->expects(static::once())->method('addActivity')
      ->with(22, $event->getApplicationProcess(), $activity);

    $this->subscriber->onUpdated($event);
  }

  public function testOnUpdatedNoStatusChange(): void {
    $event = $this->createEvent('status', 'status');

    $this->optionsLoaderMock->expects(static::never())->method('getOptionLabel');
    $this->activityManagerMock->expects(static::never())->method('addActivity');

    $this->subscriber->onUpdated($event);
  }

  private function createEvent(string $oldStatus, string $newStatus): ClearingProcessUpdatedEvent {
    $applicationProcessValues = [
      'title' => 'Title',
      'identifier' => 'Identifier',
    ];

    return new ClearingProcessUpdatedEvent(
      ClearingProcessFactory::create($applicationProcessValues + ['status' => $oldStatus]),
      ClearingProcessBundleFactory::create(['status' => $newStatus], $applicationProcessValues),
    );
  }

}
