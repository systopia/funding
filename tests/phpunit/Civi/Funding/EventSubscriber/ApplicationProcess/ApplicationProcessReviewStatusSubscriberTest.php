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

use Civi\Funding\ActivityTypeIds;
use Civi\Funding\ApplicationProcess\ApplicationProcessActivityManager;
use Civi\Funding\Entity\ActivityEntity;
use Civi\Funding\EntityFactory\ApplicationProcessBundleFactory;
use Civi\Funding\EntityFactory\ApplicationProcessFactory;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessUpdatedEvent;
use Civi\RemoteTools\RequestContext\RequestContextInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\EventSubscriber\ApplicationProcess\ApplicationProcessReviewStatusSubscriber
 */
final class ApplicationProcessReviewStatusSubscriberTest extends TestCase {

  /**
   * @var \Civi\Funding\ApplicationProcess\ApplicationProcessActivityManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $activityManagerMock;

  private ApplicationProcessReviewStatusSubscriber $subscriber;

  protected function setUp(): void {
    parent::setUp();
    $this->activityManagerMock = $this->createMock(ApplicationProcessActivityManager::class);
    $requestContextMock = $this->createMock(RequestContextInterface::class);
    $this->subscriber = new ApplicationProcessReviewStatusSubscriber(
      $this->activityManagerMock,
      $requestContextMock,
    );

    $requestContextMock->method('getContactId')->willReturn(111);
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

  public function testOnUpdatedIsReviewCalculative(): void {
    $event = $this->createEvent(NULL, FALSE, TRUE, TRUE);
    $activity = ActivityEntity::fromArray([
      'activity_type_id' => ActivityTypeIds::FUNDING_APPLICATION_REVIEW_STATUS_CHANGE,
      'subject' => 'Funding Application Review Status Changed',
      'details' => '<ul><li>Application: Title (Identifier)</li>'
      . '<li>From review content: Passed</li><li>To review content: Passed</li>'
      . '<li>From review calculative: Undecided</li><li>To review calculative: Failed</li></ul>',
      'funding_application_review_status_change.from_is_review_calculative' => NULL,
      'funding_application_review_status_change.to_is_review_calculative' => FALSE,
      'funding_application_review_status_change.from_is_review_content' => TRUE,
      'funding_application_review_status_change.to_is_review_content' => TRUE,
    ]);

    $this->activityManagerMock->expects(static::once())->method('addActivity')
      ->with(111, $event->getApplicationProcess(), $activity);

    $this->subscriber->onUpdated($event);
  }

  public function testOnUpdatedIsReviewContent(): void {
    $event = $this->createEvent(FALSE, FALSE, NULL, TRUE);
    $activity = ActivityEntity::fromArray([
      'activity_type_id' => ActivityTypeIds::FUNDING_APPLICATION_REVIEW_STATUS_CHANGE,
      'subject' => 'Funding Application Review Status Changed',
      'details' => '<ul><li>Application: Title (Identifier)</li>'
      . '<li>From review content: Undecided</li><li>To review content: Passed</li>'
      . '<li>From review calculative: Failed</li><li>To review calculative: Failed</li></ul>',
      'funding_application_review_status_change.from_is_review_calculative' => FALSE,
      'funding_application_review_status_change.to_is_review_calculative' => FALSE,
      'funding_application_review_status_change.from_is_review_content' => NULL,
      'funding_application_review_status_change.to_is_review_content' => TRUE,
    ]);

    $this->activityManagerMock->expects(static::once())->method('addActivity')
      ->with(111, $event->getApplicationProcess(), $activity);

    $this->subscriber->onUpdated($event);
  }

  public function testOnUpdatedNoReviewStatusChange(): void {
    $event = $this->createEvent(NULL, NULL, FALSE, FALSE);

    $this->activityManagerMock->expects(static::never())->method('addActivity');

    $this->subscriber->onUpdated($event);
  }

  private function createEvent(
    ?bool $oldIsCalculativeReview,
    ?bool $newIsCalculativeReview,
    ?bool $oldIsContentReview,
    ?bool $newIsContentReview
  ): ApplicationProcessUpdatedEvent {
    $applicationProcessValues = [
      'title' => 'Title',
      'identifier' => 'Identifier',
    ];

    return new ApplicationProcessUpdatedEvent(
      ApplicationProcessFactory::createApplicationProcess($applicationProcessValues + [
        'is_review_calculative' => $oldIsCalculativeReview,
        'is_review_content' => $oldIsContentReview,
      ]),
      ApplicationProcessBundleFactory::createApplicationProcessBundle($applicationProcessValues + [
        'is_review_calculative' => $newIsCalculativeReview,
        'is_review_content' => $newIsContentReview,
      ]),
    );
  }

}
