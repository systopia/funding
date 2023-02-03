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

use Civi\Funding\ApplicationProcess\ApplicationProcessManager;
use Civi\Funding\EntityFactory\ApplicationProcessBundleFactory;
use Civi\Funding\Event\ApplicationProcess\ApplicationFormSubmitSuccessEvent;
use Civi\Funding\Mock\Form\FundingCaseType\TestValidatedData;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\EventSubscriber\ApplicationProcess\ApplicationProcessReviewAssignmentSubscriber
 */
final class ApplicationProcessReviewAssignmentSubscriberTest extends TestCase {

  /**
   * @var \Civi\Funding\ApplicationProcess\ApplicationProcessManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $applicationProcessManagerMock;

  private ApplicationProcessReviewAssignmentSubscriber $subscriber;

  protected function setUp(): void {
    parent::setUp();
    $this->applicationProcessManagerMock = $this->createMock(ApplicationProcessManager::class);
    $this->subscriber = new ApplicationProcessReviewAssignmentSubscriber(
      $this->applicationProcessManagerMock,
    );
  }

  public function testGetSubscribedEvents(): void {
    $expectedSubscriptions = [
      ApplicationFormSubmitSuccessEvent::class => 'onFormSubmitSuccess',
    ];

    static::assertEquals($expectedSubscriptions, $this->subscriber::getSubscribedEvents());

    foreach ($expectedSubscriptions as $method) {
      static::assertTrue(method_exists(get_class($this->subscriber), $method));
    }
  }

  public function testReviewerCalculativeContactChange(): void {
    $event = new ApplicationFormSubmitSuccessEvent(
      1,
      ApplicationProcessBundleFactory::createApplicationProcessBundle(
        [],
        ['permissions' => ['review_calculative']],
      ),
      [],
      new TestValidatedData(['action' => 'review']),
    );

    $this->applicationProcessManagerMock->expects(static::once())->method('update')
      ->with(1, $event->getApplicationProcessBundle());
    $this->subscriber->onFormSubmitSuccess($event);

    static::assertSame(1, $event->getApplicationProcess()->getReviewerCalculativeContactId());
    static::assertNull($event->getApplicationProcess()->getReviewerContentContactId());
  }

  public function testReviewerContentContactChange(): void {
    $event = new ApplicationFormSubmitSuccessEvent(
      1,
      ApplicationProcessBundleFactory::createApplicationProcessBundle(
        [],
        ['permissions' => ['review_content']],
      ),
      [],
      new TestValidatedData(['action' => 'review']),
    );

    $this->applicationProcessManagerMock->expects(static::once())->method('update')
      ->with(1, $event->getApplicationProcessBundle());
    $this->subscriber->onFormSubmitSuccess($event);

    static::assertSame(1, $event->getApplicationProcess()->getReviewerContentContactId());
    static::assertNull($event->getApplicationProcess()->getReviewerCalculativeContactId());
  }

  public function testNoReviewAction(): void {
    $event = new ApplicationFormSubmitSuccessEvent(
      1,
      ApplicationProcessBundleFactory::createApplicationProcessBundle(
        [],
        ['permissions' => ['review_calculative', 'review_content']],
      ),
      [],
      new TestValidatedData(['action' => 'some-action']),
    );

    $this->applicationProcessManagerMock->expects(static::never())->method('update');
    $this->subscriber->onFormSubmitSuccess($event);

    static::assertNull($event->getApplicationProcess()->getReviewerCalculativeContactId());
    static::assertNull($event->getApplicationProcess()->getReviewerContentContactId());
  }

}
