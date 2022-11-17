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
    static::assertSame(
      $expectedStatus,
      $this->statusDeterminer->getStatus($fullStatus, $action),
      sprintf('Current status: %s, Action: %s, Expected status: %s', $currentStatus, $action, $expectedStatus)
    );
  }

  /**
   * @dataProvider provideActions
   */
  public function testGetStatusDecorated(): void {
    $fullStatus = new FullApplicationProcessStatus('foo', NULL, NULL);
    $this->decoratedStatusDeterminerMock->method('getStatus')
      ->with($fullStatus, 'action')
      ->willReturn('bar');
    static::assertSame('bar', $this->statusDeterminer->getStatus($fullStatus, 'action'));
  }

  /**
   * @phpstan-return iterable<array{string, string, string}>
   */
  public function provideActions(): iterable {
    yield ['approved', 'request-rework', 'rework-requested'];
    yield ['rework-requested', 'withdraw-rework-request', 'approved'];
    yield ['rework-requested', 'approve-rework-request', 'rework'];
    yield ['rework-requested', 'reject-rework-request', 'approved'];
    yield ['rework-requested', 'update', 'rework-requested'];
    yield ['rework', 'save', 'rework'];
    yield ['rework', 'apply', 'rework-review-requested'];
    yield ['rework', 'withdraw-change', 'applied'];
    yield ['rework', 'update', 'rework'];
    yield ['rework-review-requested', 'request-rework', 'rework'];
    yield ['rework-review-requested', 'review', 'rework-review'];
    yield ['rework-review', 'set-calculative-review-result', 'rework-review'];
    yield ['rework-review', 'set-content-review-result', 'rework-review'];
    yield ['rework-review', 'request-change', 'rework'];
    yield ['rework-review', 'approve-change', 'approved'];
    yield ['rework-review', 'reject-change', 'approved'];
  }

}
