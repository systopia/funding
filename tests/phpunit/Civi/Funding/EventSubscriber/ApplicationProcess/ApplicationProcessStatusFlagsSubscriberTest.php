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

use Civi\Funding\ApplicationProcess\ActionStatusInfo\ApplicationProcessActionStatusInfoContainer;
use Civi\Funding\ApplicationProcess\ActionStatusInfo\ApplicationProcessActionStatusInfoInterface;
use Civi\Funding\EntityFactory\ApplicationProcessBundleFactory;
use Civi\Funding\EntityFactory\ApplicationProcessFactory;
use Civi\Funding\EntityFactory\FundingCaseTypeFactory;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessPreCreateEvent;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessPreUpdateEvent;
use Civi\Funding\Mock\Psr\PsrContainer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\EventSubscriber\ApplicationProcess\ApplicationProcessStatusFlagsSubscriber
 */
final class ApplicationProcessStatusFlagsSubscriberTest extends TestCase {

  /**
   * @var \Civi\Funding\ApplicationProcess\ActionStatusInfo\ApplicationProcessActionStatusInfoInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $infoMock;

  private ApplicationProcessStatusFlagsSubscriber $subscriber;

  protected function setUp(): void {
    parent::setUp();
    $this->infoMock = $this->createMock(ApplicationProcessActionStatusInfoInterface::class);
    $infoContainer = new ApplicationProcessActionStatusInfoContainer(new PsrContainer([
      FundingCaseTypeFactory::DEFAULT_NAME => $this->infoMock,
    ]));
    $this->subscriber = new ApplicationProcessStatusFlagsSubscriber($infoContainer);
  }

  public function testGetSubscribedEvents(): void {
    $expectedSubscriptions = [
      ApplicationProcessPreCreateEvent::class => ['onPreCreate', -100],
      ApplicationProcessPreUpdateEvent::class => ['onPreUpdate', -100],
    ];

    static::assertEquals($expectedSubscriptions, $this->subscriber::getSubscribedEvents());

    foreach ($expectedSubscriptions as [$method, $priority]) {
      static::assertTrue(method_exists(get_class($this->subscriber), $method));
    }
  }

  public function testOnPreCreate(): void {
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle([
      'status' => 'test',
      'is_eligible' => NULL,
      'is_in_work' => TRUE,
      'is_withdrawn' => FALSE,
      'is_rejected' => TRUE,
    ]);

    $this->infoMock->expects(static::once())->method('isEligibleStatus')
      ->with('test')
      ->willReturn(TRUE);
    $this->infoMock->expects(static::once())->method('isInWorkStatus')
      ->with('test')
      ->willReturn(FALSE);
    $this->infoMock->expects(static::once())->method('isRejectedStatus')
      ->with('test')
      ->willReturn(TRUE);
    $this->infoMock->expects(static::once())->method('isWithdrawnStatus')
      ->with('test')
      ->willReturn(FALSE);

    $event = new ApplicationProcessPreCreateEvent(1, $applicationProcessBundle);
    $this->subscriber->onPreCreate($event);
    $applicationProcess = $applicationProcessBundle->getApplicationProcess();
    static::assertTrue($applicationProcess->getIsEligible());
    static::assertFalse($applicationProcess->getIsInWork());
    static::assertTrue($applicationProcess->getIsRejected());
    static::assertFalse($applicationProcess->getIsWithdrawn());
  }

  public function testOnPreUpdate(): void {
    $previousApplicationProcess = ApplicationProcessFactory::createApplicationProcess();
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle([
      'status' => 'test',
      'is_eligible' => TRUE,
    ]);

    $this->infoMock->expects(static::once())->method('isEligibleStatus')
      ->with('test')
      ->willReturn(NULL);

    $event = new ApplicationProcessPreUpdateEvent(1, $previousApplicationProcess, $applicationProcessBundle);
    $this->subscriber->onPreUpdate($event);
    static::assertNull($applicationProcessBundle->getApplicationProcess()->getIsEligible());
  }

}
