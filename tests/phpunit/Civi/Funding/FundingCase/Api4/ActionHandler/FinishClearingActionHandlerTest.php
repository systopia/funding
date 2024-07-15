<?php
/*
 * Copyright (C) 2024 SYSTOPIA GmbH
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

namespace Civi\Funding\FundingCase\Api4\ActionHandler;

use Civi\Funding\Api4\Action\FundingCase\FinishClearingAction;
use Civi\Funding\ApplicationProcess\ApplicationProcessManager;
use Civi\Funding\Entity\FullApplicationProcessStatus;
use Civi\Funding\EntityFactory\FundingCaseFactory;
use Civi\Funding\EntityFactory\FundingCaseTypeFactory;
use Civi\Funding\EntityFactory\FundingProgramFactory;
use Civi\Funding\FundingCase\Command\FundingCaseFinishClearingCommand;
use Civi\Funding\FundingCase\FundingCaseManager;
use Civi\Funding\FundingCase\Handler\FundingCaseFinishClearingHandlerInterface;
use Civi\Funding\FundingProgram\FundingCaseTypeManager;
use Civi\Funding\FundingProgram\FundingProgramManager;
use Civi\Funding\Traits\CreateMockTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\FundingCase\Api4\ActionHandler\FinishClearingActionHandler
 */
final class FinishClearingActionHandlerTest extends TestCase {

  use CreateMockTrait;

  private FinishClearingActionHandler $actionHandler;

  /**
   * @var \Civi\Funding\ApplicationProcess\ApplicationProcessManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $applicationProcessManagerMock;

  /**
   * @var \Civi\Funding\FundingCase\Handler\FundingCaseFinishClearingHandlerInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $finishClearingHandlerMock;

  /**
   * @var \Civi\Funding\FundingCase\FundingCaseManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $fundingCaseManagerMock;

  /**
   * @var \Civi\Funding\FundingProgram\FundingCaseTypeManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $fundingCaseTypeManagerMock;

  /**
   * @var \Civi\Funding\FundingProgram\FundingProgramManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $fundingProgramManagerMock;

  protected function setUp(): void {
    parent::setUp();

    $this->applicationProcessManagerMock = $this->createMock(ApplicationProcessManager::class);
    $this->finishClearingHandlerMock = $this->createMock(FundingCaseFinishClearingHandlerInterface::class);
    $this->fundingCaseManagerMock = $this->createMock(FundingCaseManager::class);
    $this->fundingCaseTypeManagerMock = $this->createMock(FundingCaseTypeManager::class);
    $this->fundingProgramManagerMock = $this->createMock(FundingProgramManager::class);

    $this->actionHandler = new FinishClearingActionHandler(
      $this->applicationProcessManagerMock,
      $this->finishClearingHandlerMock,
      $this->fundingCaseManagerMock,
      $this->fundingCaseTypeManagerMock,
      $this->fundingProgramManagerMock
    );
  }

  public function testFinishClearing(): void {
    $action = $this->createApi4ActionMock(FinishClearingAction::class);
    $action->setId(23);

    $fundingCase = FundingCaseFactory::createFundingCase();
    $this->fundingCaseManagerMock->method('get')
      ->with(23)
      ->willReturn($fundingCase);

    $fundingCaseType = FundingCaseTypeFactory::createFundingCaseType();
    $this->fundingCaseTypeManagerMock->method('get')
      ->with($fundingCase->getFundingCaseTypeId())
      ->willReturn($fundingCaseType);

    $fundingProgram = FundingProgramFactory::createFundingProgram();
    $this->fundingProgramManagerMock->method('get')
      ->with($fundingCase->getFundingProgramId())
      ->willReturn($fundingProgram);

    $applicationProcessStatusList = [24 => new FullApplicationProcessStatus('eligible', TRUE, TRUE)];
    $this->applicationProcessManagerMock->method('getStatusListByFundingCaseId')
      ->with($fundingCase->getId())
      ->willReturn($applicationProcessStatusList);

    $this->finishClearingHandlerMock->expects(self::once())->method('handle')
      ->with(new FundingCaseFinishClearingCommand(
        $fundingCase,
        $applicationProcessStatusList,
        $fundingCaseType,
        $fundingProgram
      )
    );

    $this->actionHandler->finishClearing($action);
  }

}
