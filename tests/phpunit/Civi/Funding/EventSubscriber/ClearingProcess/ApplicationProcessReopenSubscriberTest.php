<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
 *
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License as published by the Free
 * Software Foundation, either version 3 of the License, or (at your option) any
 * later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types = 1);

namespace Civi\Funding\EventSubscriber\ClearingProcess;

use Civi\Funding\ActivityTypeNames;
use Civi\Funding\ApplicationProcess\ApplicationProcessActivityManager;
use Civi\Funding\ClearingProcess\ClearingProcessManager;
use Civi\Funding\Entity\ActivityEntity;
use Civi\Funding\Entity\ClearingProcessEntityBundle;
use Civi\Funding\EntityFactory\ApplicationProcessBundleFactory;
use Civi\Funding\EntityFactory\ApplicationProcessFactory;
use Civi\Funding\EntityFactory\ClearingProcessFactory;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessUpdatedEvent;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\EventSubscriber\ClearingProcess\ApplicationProcessReopenSubscriber
 */
final class ApplicationProcessReopenSubscriberTest extends TestCase {

  private MockObject&ApplicationProcessActivityManager $activityManagerMock;

  private ClearingProcessManager&MockObject $clearingProcessManagerMock;

  private ApplicationProcessReopenSubscriber $subscriber;

  protected function setUp(): void {
    parent::setUp();
    $this->activityManagerMock = $this->createMock(ApplicationProcessActivityManager::class);
    $this->clearingProcessManagerMock = $this->createMock(ClearingProcessManager::class);
    $this->subscriber = new ApplicationProcessReopenSubscriber(
      $this->activityManagerMock,
      $this->clearingProcessManagerMock
    );
  }

  public function testGetSubscribedEvents(): void {
    $expectedSubscriptions = [ApplicationProcessUpdatedEvent::class => 'onUpdated'];

    static::assertEquals($expectedSubscriptions, $this->subscriber::getSubscribedEvents());

    foreach ($expectedSubscriptions as $method) {
      static::assertTrue(method_exists(get_class($this->subscriber), $method));
    }
  }

  public function testOnUpdatedNotReopened(): void {
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle([
      'is_withdrawn' => TRUE,
      'is_rejected' => FALSE,
    ]);
    $previousApplicationProcess = ApplicationProcessFactory::createApplicationProcess([
      'is_withdrawn' => FALSE,
      'is_rejected' => FALSE,
    ]);
    $event = new ApplicationProcessUpdatedEvent($previousApplicationProcess, $applicationProcessBundle);

    $this->clearingProcessManagerMock->expects(static::never())->method('getByApplicationProcessId');
    $this->activityManagerMock->expects(static::never())->method('getLastByApplicationProcessAndType');

    $this->subscriber->onUpdated($event);
  }

  /**
   * @param array{is_rejected: bool, is_withdrawn: bool} $applicationProcessValues
   * @param array{is_rejected: bool, is_withdrawn: bool} $previousApplicationProcessValues
   *
   * @dataProvider provideReopenedValues
   */
  public function testOnUpdatedReopened(
    array $applicationProcessValues,
    array $previousApplicationProcessValues
  ): void {
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle(
      $applicationProcessValues
    );
    $previousApplicationProcess = ApplicationProcessFactory::createApplicationProcess(
      $previousApplicationProcessValues
    );
    $event = new ApplicationProcessUpdatedEvent($previousApplicationProcess, $applicationProcessBundle);

    $clearingProcess = ClearingProcessFactory::create(['status' => 'rejected']);
    $clearingProcessBundle = new ClearingProcessEntityBundle($clearingProcess, $applicationProcessBundle);
    $this->clearingProcessManagerMock->expects(static::once())->method('getByApplicationProcessId')
      ->with($applicationProcessBundle->getApplicationProcess()->getId())
      ->willReturn($clearingProcess);

    $this->activityManagerMock->expects(static::once())->method('getLastByApplicationProcessAndType')
      ->with(
        $applicationProcessBundle->getApplicationProcess()->getId(),
        ActivityTypeNames::FUNDING_CLEARING_STATUS_CHANGE
      )
      ->willReturn(ActivityEntity::fromArray([
        'activity_type_id' => 123,
        'subject' => 'test',
        'funding_clearing_status_change.from_status' => 'previous_status',
      ]));

    $this->clearingProcessManagerMock->expects(static::once())->method('update')
      ->with($clearingProcessBundle);

    $this->subscriber->onUpdated($event);
    static::assertSame('previous_status', $clearingProcess->getStatus());
  }

  /**
   * @param array{is_rejected: bool, is_withdrawn: bool} $applicationProcessValues
   * @param array{is_rejected: bool, is_withdrawn: bool} $previousApplicationProcessValues
   *
   * @dataProvider provideReopenedValues
   */
  public function testOnUpdatedReopenedWithPreviousStatusAccepted(
    array $applicationProcessValues,
    array $previousApplicationProcessValues
  ): void {
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle(
      $applicationProcessValues
    );
    $previousApplicationProcess = ApplicationProcessFactory::createApplicationProcess(
      $previousApplicationProcessValues
    );
    $event = new ApplicationProcessUpdatedEvent($previousApplicationProcess, $applicationProcessBundle);

    $clearingProcess = ClearingProcessFactory::create(['status' => 'rejected']);
    $clearingProcessBundle = new ClearingProcessEntityBundle($clearingProcess, $applicationProcessBundle);
    $this->clearingProcessManagerMock->expects(static::once())->method('getByApplicationProcessId')
      ->with($applicationProcessBundle->getApplicationProcess()->getId())
      ->willReturn($clearingProcess);

    $this->activityManagerMock->expects(static::once())->method('getLastByApplicationProcessAndType')
      ->with(
        $applicationProcessBundle->getApplicationProcess()->getId(),
        ActivityTypeNames::FUNDING_CLEARING_STATUS_CHANGE
      )
      ->willReturn(ActivityEntity::fromArray([
        'activity_type_id' => 123,
        'subject' => 'test',
        'funding_clearing_status_change.from_status' => 'accepted',
      ]));

    $this->clearingProcessManagerMock->expects(static::once())->method('update')
      ->with($clearingProcessBundle);

    $this->subscriber->onUpdated($event);
    static::assertSame('review', $clearingProcess->getStatus());
  }

  /**
   * @phpstan-return iterable<array{
   *   array{is_rejected: bool, is_withdrawn: bool},
   *   array{is_rejected: bool, is_withdrawn: bool},
   * }>
   */
  public static function provideReopenedValues(): iterable {
    yield [
      [
        'is_rejected' => FALSE,
        'is_withdrawn' => FALSE,
      ],
      [
        'is_rejected' => TRUE,
        'is_withdrawn' => FALSE,
      ],
    ];

    yield [
      [
        'is_rejected' => FALSE,
        'is_withdrawn' => FALSE,
      ],
      [
        'is_rejected' => FALSE,
        'is_withdrawn' => TRUE,
      ],
    ];
  }

}
