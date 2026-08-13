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

use Civi\Funding\ActivityTypeNames;
use Civi\Funding\ApplicationProcess\ApplicationProcessActivityManager;
use Civi\Funding\Entity\ActivityEntity;
use Civi\Funding\EntityFactory\ApplicationProcessBundleFactory;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessUpdatedEvent;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\EventSubscriber\ApplicationProcess\ApplicationProcessMovedSubscriber
 */
final class ApplicationProcessMovedSubscriberTest extends TestCase {

  private MockObject&ApplicationProcessActivityManager $activityManagerMock;

  private ApplicationProcessMovedSubscriber $subscriber;

  protected function setUp(): void {
    parent::setUp();
    $this->activityManagerMock = $this->createMock(ApplicationProcessActivityManager::class);
    $this->subscriber = new ApplicationProcessMovedSubscriber(
      $this->activityManagerMock,
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
    $event = new ApplicationProcessUpdatedEvent(
      ApplicationProcessBundleFactory::create(
        ['identifier' => 'old-identifier', 'title' => 'Title'],
        fundingCaseValues: ['id' => 12, 'identifier' => 'old-case-identifier']
      ),
      ApplicationProcessBundleFactory::create(
        ['identifier' => 'new-identifier'],
        fundingCaseValues: ['id' => 34, 'identifier' => 'new-case-identifier']
      ),
    );

    $activity = ActivityEntity::fromArray([
      'activity_type_id:name' => ActivityTypeNames::FUNDING_APPLICATION_MOVE,
      'subject' => 'Funding Application Moved',
      // phpcs:ignore Generic.Files.LineLength.TooLong
      'details' => '<ul><li>Application: Title (new-identifier)</li><li>Previous identifier: old-identifier</li><li>From funding case: old-case-identifier</li><li>To funding case: new-case-identifier</li></ul>',
      'funding_application_move.previous_application_process_identifier' => 'old-identifier',
      'funding_application_move.from_funding_case_identifier' => 'old-case-identifier',
      'funding_application_move.to_funding_case_identifier' => 'new-case-identifier',
    ]);

    $this->activityManagerMock->expects(static::once())->method('addActivity')
      ->with($event->getApplicationProcess(), $activity);

    $this->subscriber->onUpdated($event);
  }

  public function testOnUpdated_NoFundingCaseChange(): void {
    $event = new ApplicationProcessUpdatedEvent(
      ApplicationProcessBundleFactory::create(),
      ApplicationProcessBundleFactory::create(['title' => 'New Title'])
    );

    $this->activityManagerMock->expects(static::never())->method('addActivity');

    $this->subscriber->onUpdated($event);
  }

}
