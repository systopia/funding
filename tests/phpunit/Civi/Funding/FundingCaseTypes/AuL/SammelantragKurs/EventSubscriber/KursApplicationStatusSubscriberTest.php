<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
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

namespace Civi\Funding\FundingCaseTypes\AuL\SammelantragKurs\EventSubscriber;

use Civi\Funding\ApplicationProcess\ApplicationProcessManager;
use Civi\Funding\ApplicationProcess\ApplicationSnapshotManager;
use Civi\Funding\EntityFactory\ApplicationProcessBundleFactory;
use Civi\Funding\EntityFactory\ApplicationProcessFactory;
use Civi\Funding\EntityFactory\ApplicationSnapshotFactory;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessPreUpdateEvent;
use Civi\Funding\FundingCaseTypes\AuL\SammelantragKurs\KursConstants;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\FundingCaseTypes\AuL\SammelantragKurs\EventSubscriber\KursApplicationStatusSubscriber
 */
final class KursApplicationStatusSubscriberTest extends TestCase {

  /**
   * @var \Civi\Funding\ApplicationProcess\ApplicationProcessManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $applicationProcessManagerMock;

  /**
   * @var \Civi\Funding\ApplicationProcess\ApplicationSnapshotManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $snapshotManagerMock;

  private KursApplicationStatusSubscriber $subscriber;

  protected function setUp(): void {
    parent::setUp();
    $this->applicationProcessManagerMock = $this->createMock(ApplicationProcessManager::class);
    $this->snapshotManagerMock = $this->createMock(ApplicationSnapshotManager::class);
    $this->subscriber = new KursApplicationStatusSubscriber(
      $this->applicationProcessManagerMock,
      $this->snapshotManagerMock
    );
  }

  public function testGetSubscribedEvents(): void {
    $expectedSubscriptions = [
      ApplicationProcessPreUpdateEvent::class => 'onPreUpdate',
    ];

    static::assertEquals($expectedSubscriptions, $this->subscriber::getSubscribedEvents());

    foreach ($expectedSubscriptions as $method) {
      static::assertTrue(method_exists(get_class($this->subscriber), $method));
    }
  }

  public function testTitleChanged(): void {
    $event = $this->createPreUpdateEvent(['title' => ['old', 'new']]);
    $this->subscriber->onPreUpdate($event);
    static::assertSame('rework-review-requested', $event->getApplicationProcess()->getStatus());
  }

  public function testShortDescriptionChanged(): void {
    $event = $this->createPreUpdateEvent(['short_description' => ['old', 'new']]);
    $this->subscriber->onPreUpdate($event);
    static::assertSame('rework-review-requested', $event->getApplicationProcess()->getStatus());
  }

  public function testAmountRequestedIncreasedAmountApprovedExceeded(): void {
    $event = $this->createPreUpdateEvent(['amount_requested' => [100.1, 201.0]]);
    $event->getFundingCase()->setAmountApproved(210);
    $applicationProcessId = $event->getApplicationProcess()->getId();

    $this->applicationProcessManagerMock->method('getByFundingCaseId')
      ->with($event->getFundingCase()->getId())
      ->willReturn([
        ApplicationProcessFactory::createApplicationProcess([
          'id' => $applicationProcessId + 1,
          'amount_requested' => 1.23,
          'is_eligible' => FALSE,
        ]),
        ApplicationProcessFactory::createApplicationProcess([
          'id' => $applicationProcessId + 1,
          'amount_requested' => 10.0,
        ]),
        $event->getPreviousApplicationProcess(),
      ]);

    $this->subscriber->onPreUpdate($event);
    static::assertSame('rework-review-requested', $event->getApplicationProcess()->getStatus());
  }

  public function testAmountRequestedIncreasedAmountApprovedNotExceeded(): void {
    $event = $this->createPreUpdateEvent(['amount_requested' => [100.1, 200.1]]);
    $event->getFundingCase()->setAmountApproved(210.1);
    $applicationProcessId = $event->getApplicationProcess()->getId();

    $this->applicationProcessManagerMock->method('getByFundingCaseId')
      ->with($event->getFundingCase()->getId())
      ->willReturn([
        ApplicationProcessFactory::createApplicationProcess([
          'id' => $applicationProcessId + 1,
          'amount_requested' => 1.23,
          'is_eligible' => FALSE,
        ]),
        ApplicationProcessFactory::createApplicationProcess([
          'id' => $applicationProcessId + 1,
          'amount_requested' => 10.0,
        ]),
        $event->getPreviousApplicationProcess(),
      ]);

    $this->subscriber->onPreUpdate($event);
    static::assertSame('eligible', $event->getApplicationProcess()->getStatus());
  }

  public function testStartDateChanged(): void {
    // If neither title, nor short description, nor amount requested is changed,
    // a review is not required.
    $event = $this->createPreUpdateEvent(['start_date' => [new \DateTime('2024-01-08'), new \DateTime('2024-01-09')]]);
    $event->getFundingCase()->setAmountApproved(210);

    $this->applicationProcessManagerMock->expects(static::never())->method('getByFundingCaseId');

    $this->subscriber->onPreUpdate($event);
    static::assertSame('eligible', $event->getApplicationProcess()->getStatus());
  }

  public function testNonKursFundingCase(): void {
    $event = $this->createPreUpdateEvent(
      ['start_date' => [new \DateTime('2024-01-08'), new \DateTime('2024-01-09')]],
      'FundingCaseType'
    );
    $event->getFundingCase()->setAmountApproved(210);

    $this->applicationProcessManagerMock->expects(static::never())->method('getByFundingCaseId');

    $this->subscriber->onPreUpdate($event);
    static::assertSame('rework-review-requested', $event->getApplicationProcess()->getStatus());
  }

  public function testNotReworkReviewRequested(): void {
    $event = $this->createPreUpdateEvent([
      'status' => ['draft', 'applied'],
      'start_date' => [new \DateTime('2024-01-08'), new \DateTime('2024-01-09')],
    ]);
    $event->getFundingCase()->setAmountApproved(210);

    $this->applicationProcessManagerMock->expects(static::never())->method('getByFundingCaseId');

    $this->subscriber->onPreUpdate($event);
    static::assertSame('applied', $event->getApplicationProcess()->getStatus());
  }

  /**
   * @phpstan-param array<string, array<mixed, mixed>> $changeSet
   */
  private function createPreUpdateEvent(
    array $changeSet,
    string $fundingCaseTypeName = KursConstants::FUNDING_CASE_TYPE_NAME
  ): ApplicationProcessPreUpdateEvent {
    $changeSet['status'] ??= ['rework', 'rework-review-requested'];
    $previousValues = array_map(fn(array $oldAndNew) => $oldAndNew[0], $changeSet);
    $currentValues = array_map(fn(array $oldAndNew) => $oldAndNew[1], $changeSet);

    $previousApplicationProcess = ApplicationProcessFactory::createApplicationProcess($previousValues);
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle(
      $currentValues,
      [],
      ['name' => $fundingCaseTypeName]
    );

    $snapshot = ApplicationSnapshotFactory::createApplicationSnapshot($previousApplicationProcess->toArray());
    $this->snapshotManagerMock->method('getLastByApplicationProcessId')
      ->with($snapshot->getApplicationProcessId())
      ->willReturn($snapshot);

    return new ApplicationProcessPreUpdateEvent($previousApplicationProcess, $applicationProcessBundle);
  }

}
