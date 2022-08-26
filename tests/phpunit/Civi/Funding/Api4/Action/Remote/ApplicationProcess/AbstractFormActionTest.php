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
use Civi\Funding\Api4\Action\Remote\FundingCase\Traits\NewApplicationFormActionTrait;
use Civi\Funding\Remote\RemoteFundingEntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\PhpUnit\ClockMock;

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
  protected array $applicationProcessValues;

  /**
   * @phpstan-var array<string, mixed>
   */
  protected array $fundingCaseValues;

  /**
   * @phpstan-var array<string, mixed>
   */
  protected array $fundingCaseTypeValues;

  /**
   * @phpstan-var array<string, mixed>
   */
  protected array $fundingProgramValues;

  /**
   * @var \Civi\Funding\Remote\RemoteFundingEntityManagerInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  protected MockObject $remoteFundingEntityManagerMock;

  public static function setUpBeforeClass(): void {
    parent::setUpBeforeClass();
    ClockMock::register(__CLASS__);
    ClockMock::register(NewApplicationFormActionTrait::class);
    ClockMock::withClockMock(strtotime('1970-01-02'));
  }

  protected function setUp(): void {
    parent::setUp();
    $this->remoteFundingEntityManagerMock = $this->createMock(RemoteFundingEntityManagerInterface::class);
    $this->eventDispatcherMock = $this->createMock(CiviEventDispatcher::class);

    $this->applicationProcessValues = ['id' => 22, 'funding_case_id' => 33];
    $this->fundingCaseValues = ['id' => 33, 'funding_case_type_id' => 44, 'funding_program_id' => 55];
    $this->fundingCaseTypeValues = ['id' => 44];
    $this->fundingProgramValues = [
      'id' => 55,
      'requests_start_date' => date('Y-m-d', time() - 86400),
      'requests_end_date' => date('Y-m-d', time() + 86400),
    ];

    $this->remoteFundingEntityManagerMock->method('getById')->willReturnMap([
      ['FundingApplicationProcess', 22, static::REMOTE_CONTACT_ID, $this->applicationProcessValues],
      ['FundingCase', 33, static::REMOTE_CONTACT_ID, $this->fundingCaseValues],
      ['FundingCaseType', 44, static::REMOTE_CONTACT_ID, $this->fundingCaseTypeValues],
      ['FundingProgram', 55, static::REMOTE_CONTACT_ID, $this->fundingProgramValues],
    ]);
  }

}
