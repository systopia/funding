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

namespace Civi\Funding\FundingCase;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\FundingCase\FundingCaseStatusDeterminer
 */
final class FundingCaseStatusDeterminerTest extends TestCase {

  private FundingCaseStatusDeterminer $statusDeterminer;

  protected function setUp(): void {
    parent::setUp();
    $this->statusDeterminer = new FundingCaseStatusDeterminer();
  }

  /**
   * @dataProvider provideNonFinalApplicationProcessStatus
   */
  public function testIsClosedByApplicationProcessFalse(string $applicationProcessStatus): void {
    static::assertFalse($this->statusDeterminer->isClosedByApplicationProcess($applicationProcessStatus));
  }

  /**
   * @dataProvider provideFinalApplicationProcessStatus
   */
  public function testIsClosedByApplicationProcessTrue(string $applicationProcessStatus): void {
    static::assertTrue($this->statusDeterminer->isClosedByApplicationProcess($applicationProcessStatus));
  }

  /**
   * @phpstan-return iterable<array{string}>
   */
  public function provideFinalApplicationProcessStatus(): iterable {
    yield ['withdrawn'];
    yield ['rejected'];
  }

  /**
   * @phpstan-return iterable<array{string}>
   */
  public function provideNonFinalApplicationProcessStatus(): iterable {
    yield ['new'];
    yield ['draft'];
    yield ['applied'];
    yield ['review'];
    yield ['eligible'];
  }

}
