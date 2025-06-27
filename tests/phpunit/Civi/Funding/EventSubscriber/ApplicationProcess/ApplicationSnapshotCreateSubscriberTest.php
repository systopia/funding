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

use Civi\Funding\ApplicationProcess\Command\ApplicationSnapshotCreateCommand;
use Civi\Funding\ApplicationProcess\Handler\ApplicationSnapshotCreateHandlerInterface;
use Civi\Funding\Entity\ApplicationProcessEntityBundle;
use Civi\Funding\EntityFactory\ApplicationProcessBundleFactory;
use Civi\Funding\EntityFactory\ApplicationProcessFactory;
use Civi\Funding\EntityFactory\ApplicationSnapshotFactory;
use Civi\Funding\EntityFactory\FundingCaseTypeFactory;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessPreUpdateEvent;
use Civi\Funding\FundingCaseType\MetaData\ApplicationProcessStatus;
use Civi\Funding\FundingCaseType\MetaData\DefaultApplicationProcessStatuses;
use Civi\Funding\Mock\FundingCaseType\MetaData\FundingCaseTypeMetaDataMock;
use Civi\Funding\Mock\FundingCaseType\MetaData\FundingCaseTypeMetaDataProviderMock;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\EventSubscriber\ApplicationProcess\ApplicationSnapshotCreateSubscriber
 */
final class ApplicationSnapshotCreateSubscriberTest extends TestCase {

  private FundingCaseTypeMetaDataMock $metaDataMock;

  private ApplicationSnapshotCreateSubscriber $subscriber;

  /**
   * @var \Civi\Funding\ApplicationProcess\Handler\ApplicationSnapshotCreateHandlerInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private ApplicationSnapshotCreateHandlerInterface&MockObject $snapshotCreateHandlerMock;

  protected function setUp(): void {
    parent::setUp();
    $this->metaDataMock = new FundingCaseTypeMetaDataMock(FundingCaseTypeFactory::DEFAULT_NAME);
    $this->snapshotCreateHandlerMock = $this->createMock(ApplicationSnapshotCreateHandlerInterface::class);
    $this->subscriber = new ApplicationSnapshotCreateSubscriber(
      new FundingCaseTypeMetaDataProviderMock($this->metaDataMock),
      $this->snapshotCreateHandlerMock,
    );

    $this->metaDataMock->addApplicationProcessStatus(DefaultApplicationProcessStatuses::eligible());
  }

  public function testGetSubscribedEvents(): void {
    $expectedSubscriptions = [
      ApplicationProcessPreUpdateEvent::class => ['onPreUpdate', PHP_INT_MIN],
    ];

    static::assertEquals($expectedSubscriptions, $this->subscriber::getSubscribedEvents());

    foreach ($expectedSubscriptions as [$method]) {
      static::assertTrue(method_exists(get_class($this->subscriber), $method));
    }
  }

  public function testOnPreUpdateWithDataChange(): void {
    $event = $this->createPreUpdateEvent([
      'status' => ['eligible', 'eligible'],
      'request_data' => [['foo' => 'bar'], ['foo' => 'baz']],
    ]);

    $this->snapshotCreateHandlerMock->expects(static::once())->method('handle')
      ->with(new ApplicationSnapshotCreateCommand(
        1,
        new ApplicationProcessEntityBundle(
          $event->getPreviousApplicationProcess(),
          $event->getFundingCase(),
          $event->getFundingCaseType(),
          $event->getFundingProgram()
        )
      ));
    $this->subscriber->onPreUpdate($event);
  }

  public function testOnPreUpdateWithStatusChange(): void {
    $event = $this->createPreUpdateEvent([
      'status' => ['old-status', 'eligible'],
      'request_data' => [['foo' => 'bar'], ['foo' => 'bar']],
    ]);

    $this->snapshotCreateHandlerMock->expects(static::once())->method('handle')
      ->with(new ApplicationSnapshotCreateCommand(
        1,
        new ApplicationProcessEntityBundle(
          $event->getPreviousApplicationProcess(),
          $event->getFundingCase(),
          $event->getFundingCaseType(),
          $event->getFundingProgram()
        )
      ));
    $this->subscriber->onPreUpdate($event);
  }

  public function testOnPreUpdateNoSnapshotRequiredStatus(): void {
    $event = $this->createPreUpdateEvent([
      'status' => ['eligible', 'new-status'],
      'request_data' => [['foo' => 'bar'], ['foo' => 'baz']],
    ]);

    $this->metaDataMock->addApplicationProcessStatus(new ApplicationProcessStatus([
      'name' => 'new-status',
      'label' => 'new-status',
    ]));

    $this->snapshotCreateHandlerMock->expects(static::never())->method('handle');
    $this->subscriber->onPreUpdate($event);
  }

  public function testOnPreUpdateWhileRestore(): void {
    $event = $this->createPreUpdateEvent([
      'status' => ['old-status', 'eligible'],
      'request_data' => [['foo' => 'bar'], ['foo' => 'baz']],
    ]);
    $event->getApplicationProcess()->setRestoredSnapshot(ApplicationSnapshotFactory::createApplicationSnapshot());

    $this->snapshotCreateHandlerMock->expects(static::never())->method('handle');
    $this->subscriber->onPreUpdate($event);
  }

  /**
   * @phpstan-param array<string, array<mixed, mixed>> $changeSet
   */
  private function createPreUpdateEvent(array $changeSet): ApplicationProcessPreUpdateEvent {
    $previousValues = array_map(fn(array $oldAndNew) => $oldAndNew[0], $changeSet);
    $currentValues = array_map(fn(array $oldAndNew) => $oldAndNew[1], $changeSet);

    $previousApplicationProcess = ApplicationProcessFactory::createApplicationProcess($previousValues);
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle($currentValues);

    return new ApplicationProcessPreUpdateEvent(
      1,
      $previousApplicationProcess,
      $applicationProcessBundle,
    );
  }

}
