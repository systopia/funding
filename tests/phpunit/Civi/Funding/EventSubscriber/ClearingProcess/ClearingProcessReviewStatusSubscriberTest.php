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
use Civi\Funding\EntityFactory\ClearingProcessFactory;
use Civi\Funding\Event\ClearingProcess\ClearingProcessUpdatedEvent;
use Civi\RemoteTools\RequestContext\RequestContextInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\EventSubscriber\ClearingProcess\ClearingProcessReviewStatusSubscriber
 */
final class ClearingProcessReviewStatusSubscriberTest extends TestCase {

  /**
   * @var \Civi\Funding\ApplicationProcess\ApplicationProcessActivityManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $activityManagerMock;

  private ClearingProcessReviewStatusSubscriber $subscriber;

  protected function setUp(): void {
    parent::setUp();
    $this->activityManagerMock = $this->createMock(ApplicationProcessActivityManager::class);
    $requestContextMock = $this->createMock(RequestContextInterface::class);
    $requestContextMock->method('getContactId')->willReturn(22);
    $this->subscriber = new ClearingProcessReviewStatusSubscriber(
      $this->activityManagerMock,
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

  public function testOnUpdatedIsReviewCalculative(): void {
    $event = $this->createEvent(NULL, FALSE, TRUE, TRUE);
    $activity = ActivityEntity::fromArray([
      'activity_type_id:name' => ActivityTypeNames::FUNDING_CLEARING_REVIEW_STATUS_CHANGE,
      'subject' => 'Funding Clearing Review Status Changed',
      'details' => '<ul><li>Application: Title (Identifier)</li>'
      . '<li>From review content: Passed</li><li>To review content: Passed</li>'
      . '<li>From review calculative: Undecided</li><li>To review calculative: Failed</li></ul>',
      'funding_clearing_review_status_change.from_is_review_calculative' => NULL,
      'funding_clearing_review_status_change.to_is_review_calculative' => FALSE,
      'funding_clearing_review_status_change.from_is_review_content' => TRUE,
      'funding_clearing_review_status_change.to_is_review_content' => TRUE,
    ]);

    $this->activityManagerMock->expects(static::once())->method('addActivity')
      ->with(22, $event->getApplicationProcess(), $activity);

    $this->subscriber->onUpdated($event);
  }

  public function testOnUpdatedIsReviewContent(): void {
    $event = $this->createEvent(FALSE, FALSE, NULL, TRUE);
    $activity = ActivityEntity::fromArray([
      'activity_type_id:name' => ActivityTypeNames::FUNDING_CLEARING_REVIEW_STATUS_CHANGE,
      'subject' => 'Funding Clearing Review Status Changed',
      'details' => '<ul><li>Application: Title (Identifier)</li>'
      . '<li>From review content: Undecided</li><li>To review content: Passed</li>'
      . '<li>From review calculative: Failed</li><li>To review calculative: Failed</li></ul>',
      'funding_clearing_review_status_change.from_is_review_calculative' => FALSE,
      'funding_clearing_review_status_change.to_is_review_calculative' => FALSE,
      'funding_clearing_review_status_change.from_is_review_content' => NULL,
      'funding_clearing_review_status_change.to_is_review_content' => TRUE,
    ]);

    $this->activityManagerMock->expects(static::once())->method('addActivity')
      ->with(22, $event->getApplicationProcess(), $activity);

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
  ): ClearingProcessUpdatedEvent {
    $applicationProcessValues = [
      'title' => 'Title',
      'identifier' => 'Identifier',
    ];

    return new ClearingProcessUpdatedEvent(
      ClearingProcessFactory::create([
        'is_review_calculative' => $oldIsCalculativeReview,
        'is_review_content' => $oldIsContentReview,
      ]),
      ClearingProcessBundleFactory::create([
        'is_review_calculative' => $newIsCalculativeReview,
        'is_review_content' => $newIsContentReview,
      ], $applicationProcessValues),
    );
  }

}
