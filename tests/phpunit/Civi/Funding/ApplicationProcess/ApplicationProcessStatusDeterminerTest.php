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

namespace Civi\Funding\ApplicationProcess;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\ApplicationProcess\ApplicationProcessStatusDeterminer
 */
final class ApplicationProcessStatusDeterminerTest extends TestCase {

  private ApplicationProcessStatusDeterminer $statusDeterminer;

  protected function setUp(): void {
    parent::setUp();
    $this->statusDeterminer = new ApplicationProcessStatusDeterminer();
  }

  /**
   * @dataProvider provideActionsForNew
   */
  public function testGetStatusForNew(string $action, string $expectedStatus): void {
    static::assertSame($expectedStatus, $this->statusDeterminer->getStatusForNew($action));
  }

  /**
   * @phpstan-return iterable<array{string, string}>
   */
  public function provideActionsForNew(): iterable {
    yield ['save', 'new'];
    yield ['apply', 'applied'];
  }

  public function testGetStatusForNewInvalid(): void {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Could not determine application process status for action "test"');
    $this->statusDeterminer->getStatusForNew('test');
  }

  /**
   * @dataProvider provideActions
   */
  public function testGetStatus(string $currentStatus, string $action, string $expectedStatus): void {
    static::assertSame($expectedStatus, $this->statusDeterminer->getStatus($currentStatus, $action));
  }

  /**
   * @phpstan-return iterable<array{string, string, string}>
   */
  public function provideActions(): iterable {
    yield ['new', 'save', 'new'];
    yield ['new', 'apply', 'applied'];
    yield ['applied', 'modify', 'draft'];
    yield ['applied', 'withdraw', 'withdrawn'];
    yield ['draft', 'save', 'draft'];
    yield ['draft', 'apply', 'applied'];
    yield ['draft', 'withdraw', 'withdrawn'];
  }

  public function testGetStatusInvalid(): void {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage(
      'Could not determine application process status for action "action" and current status "status"'
    );
    $this->statusDeterminer->getStatus('status', 'action');
  }

}
