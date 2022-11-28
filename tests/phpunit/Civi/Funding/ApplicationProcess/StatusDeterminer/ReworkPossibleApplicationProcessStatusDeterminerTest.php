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
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\ApplicationProcess\StatusDeterminer\ReworkPossibleApplicationProcessStatusDeterminer
 */
final class ReworkPossibleApplicationProcessStatusDeterminerTest extends TestCase {

  /**
   * @var \Civi\Funding\ApplicationProcess\StatusDeterminer\ApplicationProcessStatusDeterminerInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $decoratedStatusDeterminerMock;

  private ReworkPossibleApplicationProcessStatusDeterminer $statusDeterminer;

  protected function setUp(): void {
    parent::setUp();
    $this->decoratedStatusDeterminerMock = $this->createMock(ApplicationProcessStatusDeterminerInterface::class);
    $this->statusDeterminer = new ReworkPossibleApplicationProcessStatusDeterminer(
      $this->decoratedStatusDeterminerMock
    );
  }

  public function testGetInitialStatus(): void {
    $this->decoratedStatusDeterminerMock->method('getInitialStatus')
      ->with('foo')
      ->willReturn('bar');
    static::assertSame('bar', $this->statusDeterminer->getInitialStatus('foo'));
  }

  /**
   * @dataProvider provideActions
   */
  public function testGetStatus(string $currentStatus, string $action, string $expectedStatus): void {
    $fullStatus = new FullApplicationProcessStatus($currentStatus, NULL, NULL);
    static::assertEquals(
      new FullApplicationProcessStatus($expectedStatus, NULL, NULL),
      $this->statusDeterminer->getStatus($fullStatus, $action),
      sprintf('Current status: %s, Action: %s, Expected status: %s', $currentStatus, $action, $expectedStatus)
    );
  }

  public function testGetStatusDecorated(): void {
    $fullStatus = new FullApplicationProcessStatus('foo', NULL, NULL);
    $expectedFullStatus = new FullApplicationProcessStatus('bar', NULL, NULL);
    $this->decoratedStatusDeterminerMock->method('getStatus')
      ->with($fullStatus, 'action')
      ->willReturn($expectedFullStatus);
    static::assertSame($expectedFullStatus, $this->statusDeterminer->getStatus($fullStatus, 'action'));
  }

  /**
   * @phpstan-return iterable<array{string, string, string}>
   */
  public function provideActions(): iterable {
    yield ['approved', 'request-rework', 'rework-requested'];
    yield ['rework-requested', 'withdraw-rework-request', 'approved'];
    yield ['rework-requested', 'approve-rework-request', 'rework'];
    yield ['rework-requested', 'reject-rework-request', 'approved'];
    yield ['rework', 'save', 'rework'];
    yield ['rework', 'apply', 'rework-review-requested'];
    yield ['rework', 'withdraw-change', 'applied'];
    yield ['rework', 'review', 'rework-review'];
    yield ['rework-review-requested', 'request-rework', 'rework'];
    yield ['rework-review-requested', 'review', 'rework-review'];
    yield ['rework-review', 'request-change', 'rework'];
    yield ['rework-review', 'approve-change', 'approved'];
    yield ['rework-review', 'update', 'rework-review'];
  }

  public function testApproveCalculative(): void {
    $currentStatus = new FullApplicationProcessStatus('rework-review', NULL, NULL);
    static::assertEquals(
      new FullApplicationProcessStatus('rework-review', TRUE, NULL),
      $this->statusDeterminer->getStatus($currentStatus, 'approve-calculative'),
    );
  }

  public function testRejectCalculative(): void {
    $currentStatus = new FullApplicationProcessStatus('rework-review', NULL, NULL);
    static::assertEquals(
      new FullApplicationProcessStatus('rework-review', FALSE, NULL),
      $this->statusDeterminer->getStatus($currentStatus, 'reject-calculative'),
    );
  }

  public function testApproveContent(): void {
    $currentStatus = new FullApplicationProcessStatus('rework-review', NULL, NULL);
    static::assertEquals(
      new FullApplicationProcessStatus('rework-review', NULL, TRUE),
      $this->statusDeterminer->getStatus($currentStatus, 'approve-content'),
    );
  }

  public function testRejectContent(): void {
    $currentStatus = new FullApplicationProcessStatus('rework-review', NULL, NULL);
    static::assertEquals(
      new FullApplicationProcessStatus('rework-review', NULL, FALSE),
      $this->statusDeterminer->getStatus($currentStatus, 'reject-content'),
    );
  }

  public function testResetReview(): void {
    $currentStatus = new FullApplicationProcessStatus('rework-review', FALSE, TRUE);
    static::assertEquals(
      new FullApplicationProcessStatus('rework', NULL, NULL),
      $this->statusDeterminer->getStatus($currentStatus, 'request-change'),
    );

    static::assertEquals(
      new FullApplicationProcessStatus('rework-review', FALSE, TRUE),
      $this->statusDeterminer->getStatus($currentStatus, 'update'),
    );
  }

  public function testRejectChange(): void {
    $currentStatus = new FullApplicationProcessStatus('rework-review', FALSE, FALSE);
    static::assertEquals(
      new FullApplicationProcessStatus('approved', TRUE, TRUE),
      $this->statusDeterminer->getStatus($currentStatus, 'reject-change'),
    );
  }

}
