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
use Civi\Funding\EntityFactory\FundingCaseFactory;
use Civi\Funding\FundingCase\Command\FundingCaseApproveCommand;
use Civi\Funding\FundingCase\FundingCaseManager;
use Civi\Funding\FundingCase\Handler\FundingCaseApproveHandlerInterface;
use Civi\Funding\FundingCase\TransferContractRouter;
use Civi\Funding\Traits\CreateMockTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\Api4\Action\FundingCase\ApproveAction
 */
final class ApproveActionTest extends TestCase {

  use CreateMockTrait;

  private ApproveAction $action;

  private ApplicationProcessManager&MockObject $applicationProcessManagerMock;

  private FundingCaseApproveHandlerInterface&MockObject $approveHandlerMock;

  private FundingCaseManager&MockObject $fundingCaseManagerMock;

  private TransferContractRouter&MockObject $transferContractRouterMock;

  protected function setUp(): void {
    parent::setUp();
    $this->applicationProcessManagerMock = $this->createMock(ApplicationProcessManager::class);
    $this->approveHandlerMock = $this->createMock(FundingCaseApproveHandlerInterface::class);
    $this->fundingCaseManagerMock = $this->createMock(FundingCaseManager::class);
    $this->transferContractRouterMock = $this->createMock(TransferContractRouter::class);
    $this->action = $this->createApi4ActionMock(
      ApproveAction::class,
      $this->applicationProcessManagerMock,
      $this->approveHandlerMock,
      $this->fundingCaseManagerMock,
      $this->transferContractRouterMock,
    );
  }

  public function test(): void {
    $this->action
      ->setId(FundingCaseFactory::DEFAULT_ID)
      ->setAmount(12.34);

    $fundingCaseBundle = FundingCaseBundleFactory::create();
    $fundingCase = $fundingCaseBundle->getFundingCase();

    $this->fundingCaseManagerMock->method('getBundle')
      ->with($fundingCase->getId())
      ->willReturn($fundingCaseBundle);

    $statusList = [22 => new FullApplicationProcessStatus('new', FALSE, FALSE)];
    $this->applicationProcessManagerMock->method('getStatusListByFundingCaseId')
      ->with($fundingCase->getId())
      ->willReturn($statusList);

    $this->approveHandlerMock->expects(static::once())->method('handle')
      ->with(new FundingCaseApproveCommand(
        $fundingCaseBundle,
        12.34,
        $statusList,
      ));

    $this->transferContractRouterMock->method('generate')
      ->with(FundingCaseFactory::DEFAULT_ID)
      ->willReturn('http://example.org/transfer-contract');

    $result = new Result();
    $this->action->_run($result);
    $fundingCase->setTransferContractUri('http://example.org/transfer-contract');
    static::assertEquals($fundingCase->toArray(), $result->getArrayCopy());
  }

}
