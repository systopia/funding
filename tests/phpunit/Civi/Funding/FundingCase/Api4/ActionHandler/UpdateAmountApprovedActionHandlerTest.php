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

use Civi\Funding\Api4\Action\FundingCase\UpdateAmountApprovedAction;
use Civi\Funding\ApplicationProcess\ApplicationProcessManager;
use Civi\Funding\Entity\FullApplicationProcessStatus;
use Civi\Funding\EntityFactory\FundingCaseBundleFactory;
use Civi\Funding\EntityFactory\FundingCaseFactory;
use Civi\Funding\FundingCase\Command\FundingCaseUpdateAmountApprovedCommand;
use Civi\Funding\FundingCase\FundingCaseManager;
use Civi\Funding\FundingCase\Handler\FundingCaseUpdateAmountApprovedHandlerInterface;
use Civi\Funding\Traits\CreateMockTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\FundingCase\Api4\ActionHandler\UpdateAmountApprovedActionHandler
 */
final class UpdateAmountApprovedActionHandlerTest extends TestCase {

  use CreateMockTrait;

  private ApplicationProcessManager&MockObject $applicationProcessManagerMock;

  private UpdateAmountApprovedActionHandler $actionHandler;

  private FundingCaseManager&MockObject $fundingCaseManagerMock;

  private FundingCaseUpdateAmountApprovedHandlerInterface&MockObject $updateAmountApprovedHandlerMock;

  protected function setUp(): void {
    parent::setUp();
    $this->applicationProcessManagerMock = $this->createMock(ApplicationProcessManager::class);
    $this->updateAmountApprovedHandlerMock = $this->createMock(FundingCaseUpdateAmountApprovedHandlerInterface::class);
    $this->fundingCaseManagerMock = $this->createMock(FundingCaseManager::class);

    $this->actionHandler = new UpdateAmountApprovedActionHandler(
      $this->applicationProcessManagerMock,
      $this->updateAmountApprovedHandlerMock,
      $this->fundingCaseManagerMock,
    );
  }

  public function testUpdateAmountApproved(): void {
    $action = $this->createApi4ActionMock(UpdateAmountApprovedAction::class);
    $action->setId(FundingCaseFactory::DEFAULT_ID)
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

    $this->updateAmountApprovedHandlerMock->expects(static::once())->method('handle')
      ->with(new FundingCaseUpdateAmountApprovedCommand(
        $fundingCaseBundle,
        12.34,
        $statusList,
      ));

    static::assertEquals($fundingCase->toArray(), $this->actionHandler->updateAmountApproved($action));
  }

}
