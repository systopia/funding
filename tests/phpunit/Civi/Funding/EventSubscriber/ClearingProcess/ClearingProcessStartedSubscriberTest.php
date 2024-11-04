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

use Civi\Funding\ActivityTypeNames;
use Civi\Funding\ApplicationProcess\ApplicationProcessActivityManager;
use Civi\Funding\Entity\ActivityEntity;
use Civi\Funding\EntityFactory\ClearingProcessBundleFactory;
use Civi\Funding\Event\ClearingProcess\ClearingProcessStartedEvent;
use Civi\RemoteTools\RequestContext\RequestContextInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\EventSubscriber\ClearingProcess\ClearingProcessStartedSubscriber
 */
final class ClearingProcessStartedSubscriberTest extends TestCase {

  /**
   * @var \Civi\Funding\ApplicationProcess\ApplicationProcessActivityManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $activityManagerMock;

  private ClearingProcessStartedSubscriber $subscriber;

  protected function setUp(): void {
    parent::setUp();
    $this->activityManagerMock = $this->createMock(ApplicationProcessActivityManager::class);
    $requestContextMock = $this->createMock(RequestContextInterface::class);
    $requestContextMock->method('getContactId')->willReturn(22);
    $this->subscriber = new ClearingProcessStartedSubscriber(
      $this->activityManagerMock, $requestContextMock
    );
  }

  public function testGetSubscribedEvents(): void {
    $expectedSubscriptions = [
      ClearingProcessStartedEvent::class => 'onStarted',
    ];

    static::assertEquals($expectedSubscriptions, $this->subscriber::getSubscribedEvents());

    foreach ($expectedSubscriptions as $method) {
      static::assertTrue(method_exists(get_class($this->subscriber), $method));
    }
  }

  public function testOnCreated(): void {
    $clearingProcessBundle = ClearingProcessBundleFactory::create(
      [],
      [
        'title' => 'Title',
        'identifier' => 'Identifier',
      ],
    );
    $event = new ClearingProcessStartedEvent($clearingProcessBundle);

    $activity = ActivityEntity::fromArray([
      'activity_type_id:name' => ActivityTypeNames::FUNDING_CLEARING_CREATE,
      'subject' => 'Funding Clearing Started',
      'details' => 'Application: Title (Identifier)',
    ]);
    $this->activityManagerMock->expects(static::once())->method('addActivity')
      ->with(22, $clearingProcessBundle->getApplicationProcess(), $activity);

    $this->subscriber->onStarted($event);
  }

}
