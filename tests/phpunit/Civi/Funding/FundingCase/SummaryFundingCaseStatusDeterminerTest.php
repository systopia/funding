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

use Civi\Funding\EntityFactory\ApplicationProcessBundleFactory;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\FundingCase\SummaryFundingCaseStatusDeterminer
 */
final class SummaryFundingCaseStatusDeterminerTest extends TestCase {

  private SummaryFundingCaseStatusDeterminer $statusDeterminer;

  protected function setUp(): void {
    parent::setUp();
    $this->statusDeterminer = new SummaryFundingCaseStatusDeterminer();
  }

  public function testGetStatus(): void {
    static::assertSame('test', $this->statusDeterminer->getStatus('test', 'do_something'));
    static::assertSame('ongoing', $this->statusDeterminer->getStatus('test', 'approve'));
  }

  public function testIsClosedByApplicationProcessTrue(): void {
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle(
      ['status' => 'sealed'],
      [],
      ['is_summary_application' => TRUE],
    );
    static::assertFalse($this->statusDeterminer->isClosedByApplicationProcess($applicationProcessBundle, 'previous'));
  }

}
