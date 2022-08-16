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

namespace Civi\Funding\Api4\Action\Remote\ApplicationProcess;

use Civi\Core\CiviEventDispatcher;
use Civi\Funding\Remote\RemoteFundingEntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

abstract class AbstractFormActionTest extends TestCase {

  protected const REMOTE_CONTACT_ID = '00';

  protected const CONTACT_ID = 11;

  /**
   * @var \Civi\Core\CiviEventDispatcher&\PHPUnit\Framework\MockObject\MockObject
   */
  protected $eventDispatcherMock;

  /**
   * @phpstan-var array<string, mixed>
   */
  protected array $applicationProcess;

  /**
   * @phpstan-var array<string, mixed>
   */
  protected array $fundingCase;

  /**
   * @phpstan-var array<string, mixed>
   */
  protected array $fundingCaseType;

  /**
   * @phpstan-var array<string, mixed>
   */
  protected array $fundingProgram;

  /**
   * @var \Civi\Funding\Remote\RemoteFundingEntityManagerInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  protected MockObject $remoteFundingEntityManagerMock;

  protected function setUp(): void {
    parent::setUp();
    $this->remoteFundingEntityManagerMock = $this->createMock(RemoteFundingEntityManagerInterface::class);
    $this->eventDispatcherMock = $this->createMock(CiviEventDispatcher::class);

    $this->applicationProcess = ['id' => 22, 'funding_case_id' => 33];
    $this->fundingCase = ['id' => 33, 'funding_case_type_id' => 44, 'funding_program_id' => 55];
    $this->fundingCaseType = ['id' => 44];
    $this->fundingProgram = ['id' => 55];

    $this->remoteFundingEntityManagerMock->method('getById')->willReturnMap([
      ['FundingApplicationProcess', 22, static::REMOTE_CONTACT_ID, static::CONTACT_ID, $this->applicationProcess],
      ['FundingCase', 33, static::REMOTE_CONTACT_ID, static::CONTACT_ID, $this->fundingCase],
      ['FundingCaseType', 44, static::REMOTE_CONTACT_ID, static::CONTACT_ID, $this->fundingCaseType],
      ['FundingProgram', 55, static::REMOTE_CONTACT_ID, static::CONTACT_ID, $this->fundingProgram],
    ]);
  }

}
