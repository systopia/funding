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
 * @covers \Civi\Funding\ApplicationProcess\StatusDeterminer\ApplicationProcessStatusDeterminer
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
    $fullStatus = new FullApplicationProcessStatus($currentStatus, NULL, NULL);
    static::assertSame(
      $expectedStatus,
      $this->statusDeterminer->getStatus($fullStatus, $action),
      sprintf('Current status: %s, Action: %s, Expected status: %s', $currentStatus, $action, $expectedStatus)
    );
  }

  /**
   * @phpstan-return iterable<array{string, string, string}>
   */
  public function provideActions(): iterable {
    yield ['new', 'save', 'new'];
    yield ['new', 'apply', 'applied'];
    yield ['new', 'update', 'new'];
    yield ['applied', 'modify', 'draft'];
    yield ['applied', 'withdraw', 'withdrawn'];
    yield ['applied', 'review', 'review'];
    yield ['applied', 'update', 'applied'];
    yield ['draft', 'save', 'draft'];
    yield ['draft', 'apply', 'applied'];
    yield ['draft', 'withdraw', 'withdrawn'];
    yield ['draft', 'update', 'draft'];
    yield ['review', 'set-calculative-review-result', 'review'];
    yield ['review', 'set-calculative-review-result', 'review'];
    yield ['review', 'request-change', 'draft'];
    yield ['review', 'approve', 'approved'];
    yield ['review', 'reject', 'rejected'];
    yield ['review', 'update', 'review'];
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
    yield [new FullApplicationProcessStatus('final', TRUE, TRUE)];
  }

}
