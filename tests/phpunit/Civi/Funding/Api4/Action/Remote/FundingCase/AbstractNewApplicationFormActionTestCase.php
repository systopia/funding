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

namespace Civi\Funding\Api4\Action\Remote\FundingCase;

use Civi\Core\CiviEventDispatcherInterface;
use Civi\Funding\Api4\Action\Remote\FundingCase\Traits\NewApplicationFormActionTrait;
use Civi\Funding\Entity\FundingCaseTypeEntity;
use Civi\Funding\Entity\FundingProgramEntity;
use Civi\Funding\EntityFactory\FundingCaseTypeFactory;
use Civi\Funding\EntityFactory\FundingProgramFactory;
use Civi\Funding\FundingProgram\FundingCaseTypeManager;
use Civi\Funding\FundingProgram\FundingCaseTypeProgramRelationChecker;
use Civi\Funding\FundingProgram\FundingProgramManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\PhpUnit\ClockMock;

abstract class AbstractNewApplicationFormActionTestCase extends TestCase {

  /**
   * @var \PHPUnit\Framework\MockObject\MockObject&\Civi\Core\CiviEventDispatcherInterface
   */
  protected MockObject $eventDispatcherMock;

  /**
   * @var \Civi\Funding\FundingProgram\FundingCaseTypeManager&\PHPUnit\Framework\MockObject\MockObject
   */
  protected MockObject $fundingCaseTypeManagerMock;

  protected FundingCaseTypeEntity $fundingCaseType;

  /**
   * @var \Civi\Funding\FundingProgram\FundingProgramManager&\PHPUnit\Framework\MockObject\MockObject
   */
  protected MockObject $fundingProgramManagerMock;

  protected FundingProgramEntity $fundingProgram;

  /**
   * @var \PHPUnit\Framework\MockObject\MockObject&\Civi\Funding\FundingProgram\FundingCaseTypeProgramRelationChecker
   */
  protected MockObject $relationCheckerMock;

  public static function setUpBeforeClass(): void {
    parent::setUpBeforeClass();
    ClockMock::register(__CLASS__);
    ClockMock::register(NewApplicationFormActionTrait::class);
    ClockMock::withClockMock(strtotime('1970-01-02'));
  }

  protected function setUp(): void {
    parent::setUp();
    $this->fundingCaseTypeManagerMock = $this->createMock(FundingCaseTypeManager::class);
    $this->fundingProgramManagerMock = $this->createMock(FundingProgramManager::class);
    $this->eventDispatcherMock = $this->createMock(CiviEventDispatcherInterface::class);
    $this->relationCheckerMock = $this->createMock(FundingCaseTypeProgramRelationChecker::class);

    $this->fundingCaseType = FundingCaseTypeFactory::createFundingCaseType(['id' => 22]);
    $this->fundingCaseTypeManagerMock->method('get')->with(22)->willReturn($this->fundingCaseType);

    $this->fundingProgram = FundingProgramFactory::createFundingProgram([
      'id' => 33,
      'requests_start_date' => date('Y-m-d', time() - 86400),
      'requests_end_date' => date('Y-m-d', time() + 86400),
      'permissions' => ['application_create'],
    ]);
    $this->fundingProgramManagerMock->method('get')->with(33)->willReturn($this->fundingProgram);
  }

}
