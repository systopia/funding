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
use Civi\Funding\FundingCase\Command\TransferContractRecreateCommand;
use Civi\Funding\FundingCase\FundingCaseManager;
use Civi\Funding\FundingCase\Handler\TransferContractRecreateHandlerInterface;
use Civi\Funding\Traits\CreateMockTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\Api4\Action\FundingCase\RecreateTransferContractAction
 */
final class RecreateTransferContractActionTest extends TestCase {

  use CreateMockTrait;

  private RecreateTransferContractAction $action;

  private ApplicationProcessManager&MockObject $applicationProcessManagerMock;

  private FundingCaseManager&MockObject $fundingCaseManagerMock;

  private TransferContractRecreateHandlerInterface&MockObject $transferContractRecreateHandlerMock;

  protected function setUp(): void {
    parent::setUp();
    $this->applicationProcessManagerMock = $this->createMock(ApplicationProcessManager::class);
    $this->fundingCaseManagerMock = $this->createMock(FundingCaseManager::class);
    $this->transferContractRecreateHandlerMock = $this->createMock(TransferContractRecreateHandlerInterface::class);
    $this->action = $this->createApi4ActionMock(
      RecreateTransferContractAction::class,
      $this->applicationProcessManagerMock,
      $this->fundingCaseManagerMock,
      $this->transferContractRecreateHandlerMock,
    );
  }

  public function test(): void {
    $this->action->setId(FundingCaseFactory::DEFAULT_ID);

    $fundingCaseBundle = FundingCaseBundleFactory::create(['amount_approved' => 12.34]);
    $this->fundingCaseManagerMock->method('getBundle')
      ->with(FundingCaseFactory::DEFAULT_ID)
      ->willReturn($fundingCaseBundle);

    $statusList = [22 => new FullApplicationProcessStatus('new', FALSE, FALSE)];
    $this->applicationProcessManagerMock->method('getStatusListByFundingCaseId')
      ->with($fundingCaseBundle->getFundingCase()->getId())
      ->willReturn($statusList);

    $this->transferContractRecreateHandlerMock->expects(static::once())->method('handle')
      ->with(new TransferContractRecreateCommand($fundingCaseBundle, $statusList));

    $result = new Result();
    $this->action->_run($result);
    static::assertEquals($fundingCaseBundle->getFundingCase()->toArray(), $result->getArrayCopy());
  }

}
