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
use Civi\Funding\EntityFactory\FundingCaseBundleFactory;
use Civi\Funding\FundingCase\Command\FundingCaseFinishClearingCommand;
use Civi\Funding\FundingCase\FundingCaseManager;
use Civi\Funding\FundingCase\Handler\FundingCaseFinishClearingHandlerInterface;
use Civi\Funding\Traits\CreateMockTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\FundingCase\Api4\ActionHandler\FinishClearingActionHandler
 */
final class FinishClearingActionHandlerTest extends TestCase {

  use CreateMockTrait;

  private FinishClearingActionHandler $actionHandler;

  private ApplicationProcessManager&MockObject $applicationProcessManagerMock;

  private FundingCaseFinishClearingHandlerInterface&MockObject $finishClearingHandlerMock;

  private FundingCaseManager&MockObject $fundingCaseManagerMock;

  protected function setUp(): void {
    parent::setUp();

    $this->applicationProcessManagerMock = $this->createMock(ApplicationProcessManager::class);
    $this->finishClearingHandlerMock = $this->createMock(FundingCaseFinishClearingHandlerInterface::class);
    $this->fundingCaseManagerMock = $this->createMock(FundingCaseManager::class);

    $this->actionHandler = new FinishClearingActionHandler(
      $this->applicationProcessManagerMock,
      $this->finishClearingHandlerMock,
      $this->fundingCaseManagerMock,
    );
  }

  public function testFinishClearing(): void {
    $action = $this->createApi4ActionMock(FinishClearingAction::class);
    $action->setId(23);

    $fundingCaseBundle = FundingCaseBundleFactory::create();
    $this->fundingCaseManagerMock->method('getBundle')
      ->with(23)
      ->willReturn($fundingCaseBundle);

    $applicationProcessStatusList = [24 => new FullApplicationProcessStatus('eligible', TRUE, TRUE)];
    $this->applicationProcessManagerMock->method('getStatusListByFundingCaseId')
      ->with($fundingCaseBundle->getFundingCase()->getId())
      ->willReturn($applicationProcessStatusList);

    $this->finishClearingHandlerMock->expects(self::once())->method('handle')
      ->with(new FundingCaseFinishClearingCommand(
        $fundingCaseBundle,
        $applicationProcessStatusList,
      )
    );

    $this->actionHandler->finishClearing($action);
  }

}
