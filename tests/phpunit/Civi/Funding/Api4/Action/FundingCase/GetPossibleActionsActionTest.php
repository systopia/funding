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

namespace Civi\Funding\Api4\Action\FundingCase;

use Civi\Api4\Generic\Result;
use Civi\Funding\ApplicationProcess\ApplicationProcessManager;
use Civi\Funding\Entity\FullApplicationProcessStatus;
use Civi\Funding\EntityFactory\FundingCaseBundleFactory;
use Civi\Funding\FundingCase\Command\FundingCasePossibleActionsGetCommand;
use Civi\Funding\FundingCase\FundingCaseManager;
use Civi\Funding\FundingCase\Handler\FundingCasePossibleActionsGetHandlerInterface;
use Civi\Funding\Traits\CreateMockTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\Api4\Action\FundingCase\GetPossibleActionsAction
 */
final class GetPossibleActionsActionTest extends TestCase {

  use CreateMockTrait;

  private GetPossibleActionsAction $action;

  private ApplicationProcessManager&MockObject $applicationProcessManagerMock;

  private FundingCaseManager&MockObject $fundingCaseManagerMock;

  private FundingCasePossibleActionsGetHandlerInterface&MockObject $possibleActionsGetHandlerMock;

  protected function setUp(): void {
    parent::setUp();
    $this->applicationProcessManagerMock = $this->createMock(ApplicationProcessManager::class);
    $this->fundingCaseManagerMock = $this->createMock(FundingCaseManager::class);
    $this->possibleActionsGetHandlerMock = $this->createMock(FundingCasePossibleActionsGetHandlerInterface::class);
    $this->action = $this->createApi4ActionMock(
      GetPossibleActionsAction::class,
      $this->applicationProcessManagerMock,
      $this->fundingCaseManagerMock,
      $this->possibleActionsGetHandlerMock,
    );
  }

  public function testRun(): void {
    $fundingCaseBundle = FundingCaseBundleFactory::create();
    $fundingCase = $fundingCaseBundle->getFundingCase();
    $statusList = [22 => new FullApplicationProcessStatus('new', FALSE, FALSE)];
    $this->fundingCaseManagerMock->method('getBundle')
      ->with($fundingCase->getId())
      ->willReturn($fundingCaseBundle);
    $this->applicationProcessManagerMock->method('getStatusListByFundingCaseId')
      ->with($fundingCase->getId())
      ->willReturn($statusList);

    $command = new FundingCasePossibleActionsGetCommand($fundingCaseBundle, $statusList);
    $this->possibleActionsGetHandlerMock->method('handle')
      ->with($command)
      ->willReturn(['possible_action']);

    $this->action->setId($fundingCase->getId());
    $result = new Result();
    $this->action->_run($result);
    static::assertSame(['possible_action'], $result->getArrayCopy());
  }

}
