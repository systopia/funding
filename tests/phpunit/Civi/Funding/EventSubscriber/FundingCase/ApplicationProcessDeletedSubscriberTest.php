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

namespace Civi\Funding\EventSubscriber\FundingCase;

use Civi\Funding\ApplicationProcess\ApplicationProcessManager;
use Civi\Funding\EntityFactory\ApplicationProcessBundleFactory;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessDeletedEvent;
use Civi\Funding\FundingCase\FundingCaseManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\EventSubscriber\FundingCase\ApplicationProcessDeletedSubscriber
 */
final class ApplicationProcessDeletedSubscriberTest extends TestCase {

  /**
   * @var \Civi\Funding\ApplicationProcess\ApplicationProcessManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $applicationProcessManagerMock;

  /**
   * @var \Civi\Funding\FundingCase\FundingCaseManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $fundingCaseManagerMock;

  private ApplicationProcessDeletedSubscriber $subscriber;

  protected function setUp(): void {
    parent::setUp();
    $this->applicationProcessManagerMock = $this->createMock(ApplicationProcessManager::class);
    $this->fundingCaseManagerMock = $this->createMock(FundingCaseManager::class);
    $this->subscriber = new ApplicationProcessDeletedSubscriber(
      $this->applicationProcessManagerMock,
      $this->fundingCaseManagerMock,
    );
  }

  public function testGetSubscribedEvents(): void {
    $expectedSubscriptions = [
      ApplicationProcessDeletedEvent::class => 'onDeleted',
    ];

    static::assertEquals($expectedSubscriptions, ApplicationProcessDeletedSubscriber::getSubscribedEvents());

    foreach ($expectedSubscriptions as $method) {
      static::assertTrue(method_exists(ApplicationProcessDeletedSubscriber::class, $method));
    }
  }

  public function testOnDeleted(): void {
    $event = $this->createEvent();
    $this->applicationProcessManagerMock->expects(static::once())->method('countByFundingCaseId')
      ->with($event->getFundingCase()->getId())
      ->willReturn(0);

    $this->fundingCaseManagerMock->expects(static::once())->method('delete')->with($event->getFundingCase());

    $this->subscriber->onDeleted($event);
  }

  public function testOnDeletedCombinedApplication(): void {
    $event = $this->createEvent(TRUE);
    $this->applicationProcessManagerMock->method('countByFundingCaseId')
      ->with($event->getFundingCase()->getId())
      ->willReturn(0);

    $this->fundingCaseManagerMock->expects(static::never())->method('delete')->with($event->getFundingCase());

    $this->subscriber->onDeleted($event);
  }

  public function testOnDeletedRemainingApplicationProcess(): void {
    $event = $this->createEvent();
    $this->applicationProcessManagerMock->expects(static::once())->method('countByFundingCaseId')
      ->with($event->getFundingCase()->getId())
      ->willReturn(1);

    $this->fundingCaseManagerMock->expects(static::never())->method('delete');

    $this->subscriber->onDeleted($event);
  }

  private function createEvent(bool $combinedApplication = FALSE): ApplicationProcessDeletedEvent {
    return new ApplicationProcessDeletedEvent(
      ApplicationProcessBundleFactory::createApplicationProcessBundle(
        [],
        [],
        ['is_combined_application' => $combinedApplication],
      )
    );
  }

}
