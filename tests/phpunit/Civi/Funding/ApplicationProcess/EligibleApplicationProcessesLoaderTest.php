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

namespace Civi\Funding\ApplicationProcess;

use Civi\Funding\EntityFactory\ApplicationProcessFactory;
use Civi\Funding\EntityFactory\FundingCaseFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\ApplicationProcess\EligibleApplicationProcessesLoader
 */
final class EligibleApplicationProcessesLoaderTest extends TestCase {

  /**
   * @var \Civi\Funding\ApplicationProcess\ApplicationProcessManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $applicationProcessManagerMock;

  private EligibleApplicationProcessesLoader $loader;

  protected function setUp(): void {
    parent::setUp();
    $this->applicationProcessManagerMock = $this->createMock(ApplicationProcessManager::class);
    $this->loader = new EligibleApplicationProcessesLoader($this->applicationProcessManagerMock);
  }

  public function test(): void {
    $applicationProcessEligibilityUndecided = ApplicationProcessFactory::createApplicationProcess([
      'is_eligible' => NULL,
    ]);
    $applicationProcessIneligible = ApplicationProcessFactory::createApplicationProcess(['is_eligible' => FALSE]);
    $applicationProcessEligible = ApplicationProcessFactory::createApplicationProcess(['is_eligible' => TRUE]);

    $fundingCase = FundingCaseFactory::createFundingCase();
    $this->applicationProcessManagerMock->method('getByFundingCaseId')
      ->with($fundingCase->getId())
      ->willReturn([
        $applicationProcessEligibilityUndecided,
        $applicationProcessIneligible,
        $applicationProcessEligible,
      ]);

    static::assertSame([$applicationProcessEligible], $this->loader->getEligibleProcessesForContract($fundingCase));
  }

}
