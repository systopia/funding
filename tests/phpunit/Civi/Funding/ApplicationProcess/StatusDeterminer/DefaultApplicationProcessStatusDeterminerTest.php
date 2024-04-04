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

namespace Civi\Funding\ApplicationProcess\StatusDeterminer;

use Civi\Funding\Entity\FullApplicationProcessStatus;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\ApplicationProcess\StatusDeterminer\AbstractApplicationProcessStatusDeterminer
 * @covers \Civi\Funding\ApplicationProcess\StatusDeterminer\DefaultApplicationProcessStatusDeterminer
 */
final class DefaultApplicationProcessStatusDeterminerTest extends TestCase {

  private DefaultApplicationProcessStatusDeterminer $statusDeterminer;

  protected function setUp(): void {
    parent::setUp();
    $this->statusDeterminer = new DefaultApplicationProcessStatusDeterminer();
  }

  /**
   * @dataProvider provideInitialActions
   */
  public function testGetInitialStatus(string $action, string $expectedStatus): void {
    static::assertSame($expectedStatus, $this->statusDeterminer->getInitialStatus($action));
  }

  /**
   * @phpstan-return iterable<array{string, string}>
   */
  public function provideInitialActions(): iterable {
    yield ['save', 'new'];
    yield ['save&new', 'new'];
    yield ['save&copy', 'new'];
    yield ['apply', 'applied'];
  }

  public function testGetInitialStatusInvalid(): void {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Could not determine application process status for action "test"');
    $this->statusDeterminer->getInitialStatus('test');
  }

  /**
   * @dataProvider provideActions
   */
  public function testGetStatus(string $currentStatus, string $action, string $expectedStatus): void {
    $fullCurrentStatus = new FullApplicationProcessStatus($currentStatus, NULL, NULL);
    static::assertEquals(
      new FullApplicationProcessStatus($expectedStatus, NULL, NULL),
      $this->statusDeterminer->getStatus($fullCurrentStatus, $action),
      sprintf('Current status: %s, Action: %s, Expected status: %s', $currentStatus, $action, $expectedStatus)
    );
  }

  /**
   * @phpstan-return iterable<array{string, string, string}>
   */
  public function provideActions(): iterable {
    yield ['new', 'save', 'new'];
    yield ['new', 'apply', 'applied'];
    yield ['applied', 'modify', 'draft'];
    yield ['applied', 'withdraw', 'withdrawn'];
    yield ['applied', 'review', 'review'];
    yield ['applied', 'add-comment', 'applied'];
    yield ['draft', 'save', 'draft'];
    yield ['draft', 'apply', 'applied'];
    yield ['draft', 'withdraw', 'withdrawn'];
    yield ['draft', 'review', 'review'];
    yield ['draft', 'add-comment', 'draft'];
    yield ['review', 'request-change', 'draft'];
    yield ['review', 'approve', 'eligible'];
    yield ['review', 'reject', 'rejected'];
    yield ['review', 'update', 'review'];
    yield ['review', 'add-comment', 'review'];
    yield ['eligible', 'withdraw', 'withdrawn'];
    yield ['eligible', 'update', 'eligible'];
    yield ['eligible', 'add-comment', 'eligible'];
    yield ['complete', 'withdraw', 'withdrawn'];
  }

  public function testApproveCalculative(): void {
    $currentStatus = new FullApplicationProcessStatus('review', NULL, NULL);
    static::assertEquals(
      new FullApplicationProcessStatus('review', TRUE, NULL),
      $this->statusDeterminer->getStatus($currentStatus, 'approve-calculative'),
    );
  }

  public function testRejectCalculative(): void {
    $currentStatus = new FullApplicationProcessStatus('review', NULL, NULL);
    static::assertEquals(
      new FullApplicationProcessStatus('review', FALSE, NULL),
      $this->statusDeterminer->getStatus($currentStatus, 'reject-calculative'),
    );
  }

  public function testApproveContent(): void {
    $currentStatus = new FullApplicationProcessStatus('review', NULL, NULL);
    static::assertEquals(
      new FullApplicationProcessStatus('review', NULL, TRUE),
      $this->statusDeterminer->getStatus($currentStatus, 'approve-content'),
    );
  }

  public function testRejectContent(): void {
    $currentStatus = new FullApplicationProcessStatus('review', NULL, NULL);
    static::assertEquals(
      new FullApplicationProcessStatus('review', NULL, FALSE),
      $this->statusDeterminer->getStatus($currentStatus, 'reject-content'),
    );
  }

  public function testResetReview(): void {
    $currentStatus = new FullApplicationProcessStatus('review', TRUE, TRUE);
    static::assertEquals(
      new FullApplicationProcessStatus('draft', NULL, NULL),
      $this->statusDeterminer->getStatus($currentStatus, 'request-change'),
    );

    static::assertEquals(
      new FullApplicationProcessStatus('rejected', TRUE, TRUE),
      $this->statusDeterminer->getStatus($currentStatus, 'reject'),
    );
  }

  /**
   * @dataProvider provideFinalStatus
   */
  public function testGetStatusFinal(FullApplicationProcessStatus $fullCurrentStatus): void {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage(sprintf(
      'Could not determine application process status for action "update" and current status "%s"',
      $fullCurrentStatus->getStatus(),
    ));
    $this->statusDeterminer->getStatus($fullCurrentStatus, 'update');
  }

  /**
   * @phpstan-return iterable<array{FullApplicationProcessStatus}>
   */
  public function provideFinalStatus(): iterable {
    yield [new FullApplicationProcessStatus('withdrawn', NULL, NULL)];
    yield [new FullApplicationProcessStatus('rejected', FALSE, NULL)];
    yield [new FullApplicationProcessStatus('complete', TRUE, TRUE)];
  }

}
