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
use Civi\RemoteTools\Api4\Query\Comparison;
use Civi\RemoteTools\Api4\Query\CompositeCondition;
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
    $applicationProcessEligible = ApplicationProcessFactory::createApplicationProcess(['amount_eligible' => 1.23]);

    $fundingCase = FundingCaseFactory::createFundingCase();
    $this->applicationProcessManagerMock->method('getBy')
      ->with(CompositeCondition::new('AND',
        Comparison::new('funding_case_id', '=', $fundingCase->getId()),
        Comparison::new('amount_eligible', '>', 0)
      ))
      ->willReturn([$applicationProcessEligible]);

    static::assertSame([$applicationProcessEligible], $this->loader->getEligibleProcessesForContract($fundingCase));
  }

}
