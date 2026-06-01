<?php
/*
 * Copyright (C) 2026 SYSTOPIA GmbH
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

namespace Civi\Funding\FundingCaseTypes\AuL\Personalkosten\EventSubscriber;

use Civi\Funding\EntityFactory\ApplicationProcessBundleFactory;
use Civi\Funding\Event\ApplicationProcess\ApplicationSnapshotRestoredEvent;
use Civi\Funding\FundingCaseTypes\AuL\Personalkosten\Application\PersonalkostenApplicationProcessUpdater;
use Civi\Funding\FundingCaseTypes\AuL\Personalkosten\PersonalkostenMetaData;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\FundingCaseTypes\AuL\Personalkosten\EventSubscriber\PersonalkostenSnapshotRestoreSubscriber
 */
final class PersonalkostenSnapshotRestoreSubscriberTest extends TestCase {

  private PersonalkostenApplicationProcessUpdater&MockObject $personalkostenApplicationProcessUpdaterMock;

  private PersonalkostenSnapshotRestoreSubscriber $subscriber;

  protected function setUp(): void {
    parent::setUp();
    $this->personalkostenApplicationProcessUpdaterMock = $this->createMock(
      PersonalkostenApplicationProcessUpdater::class
    );
    $this->subscriber = new PersonalkostenSnapshotRestoreSubscriber(
      $this->personalkostenApplicationProcessUpdaterMock
    );
  }

  public function testGetSubscribedEvents(): void {
    $expectedSubscriptions = [ApplicationSnapshotRestoredEvent::class => 'onRestored'];

    static::assertEquals($expectedSubscriptions, $this->subscriber::getSubscribedEvents());

    foreach ($expectedSubscriptions as $method) {
      static::assertTrue(method_exists(get_class($this->subscriber), $method));
    }

  }

  public function testFoerderquoteChanged(): void {
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle(
      applicationProcessValues: [
        'request_data' => [
          'foerderquote' => 10,
          'sachkostenpauschale' => 50,
        ],
      ],
      fundingCaseTypeValues: ['name' => PersonalkostenMetaData::NAME],
      fundingProgramValues: [
        'funding_program_extra.foerderquote' => 20,
        'funding_program_extra.sachkostenpauschale' => 50.0,
      ]
    );

    $this->personalkostenApplicationProcessUpdaterMock
      ->expects(static::once())
      ->method('updateApplicationProcess')
      ->with($applicationProcessBundle);

    $this->subscriber->onRestored(new ApplicationSnapshotRestoredEvent($applicationProcessBundle));
  }

  public function testSachkostenpauschaleChanged(): void {
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle(
      applicationProcessValues: [
        'request_data' => [
          'foerderquote' => 10,
          'sachkostenpauschale' => 50,
        ],
      ],
      fundingCaseTypeValues: ['name' => PersonalkostenMetaData::NAME],
      fundingProgramValues: [
        'funding_program_extra.foerderquote' => 10,
        'funding_program_extra.sachkostenpauschale' => 60.0,
      ]
    );

    $this->personalkostenApplicationProcessUpdaterMock
      ->expects(static::once())
      ->method('updateApplicationProcess')
      ->with($applicationProcessBundle);

    $this->subscriber->onRestored(new ApplicationSnapshotRestoredEvent($applicationProcessBundle));
  }

  public function testNotChanged(): void {
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle(
      applicationProcessValues: [
        'request_data' => [
          'foerderquote' => 10,
          'sachkostenpauschale' => 50,
        ],
      ],
      fundingCaseTypeValues: ['name' => PersonalkostenMetaData::NAME],
      fundingProgramValues: [
        'funding_program_extra.foerderquote' => 10,
        'funding_program_extra.sachkostenpauschale' => 50.0,
      ]
    );

    $this->personalkostenApplicationProcessUpdaterMock
      ->expects(static::never())
      ->method('updateApplicationProcess');

    $this->subscriber->onRestored(new ApplicationSnapshotRestoredEvent($applicationProcessBundle));
  }

  public function testDifferentFundingCaseType(): void {
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle(
      applicationProcessValues: [
        'request_data' => [
          'foerderquote' => 10,
          'sachkostenpauschale' => 50,
        ],
      ],
      fundingCaseTypeValues: ['name' => 'test'],
      fundingProgramValues: [
        'funding_program_extra.foerderquote' => 10,
        'funding_program_extra.sachkostenpauschale' => 60.0,
      ]
    );

    $this->personalkostenApplicationProcessUpdaterMock
      ->expects(static::never())
      ->method('updateApplicationProcess');

    $this->subscriber->onRestored(new ApplicationSnapshotRestoredEvent($applicationProcessBundle));
  }

}
