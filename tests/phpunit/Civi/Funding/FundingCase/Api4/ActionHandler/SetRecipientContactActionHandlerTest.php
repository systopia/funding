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

use Civi\Funding\Api4\Action\FundingCase\SetRecipientContactAction;
use Civi\Funding\ApplicationProcess\ApplicationProcessManager;
use Civi\Funding\Entity\FullApplicationProcessStatus;
use Civi\Funding\EntityFactory\FundingCaseBundleFactory;
use Civi\Funding\EntityFactory\FundingCaseFactory;
use Civi\Funding\FundingCase\Command\FundingCaseRecipientContactSetCommand;
use Civi\Funding\FundingCase\FundingCaseManager;
use Civi\Funding\FundingCase\Handler\FundingCaseRecipientContactSetHandlerInterface;
use Civi\Funding\Traits\CreateMockTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\FundingCase\Api4\ActionHandler\SetRecipientContactActionHandler
 */
final class SetRecipientContactActionHandlerTest extends TestCase {

  use CreateMockTrait;

  private ApplicationProcessManager&MockObject $applicationProcessManagerMock;

  private SetRecipientContactActionHandler $actionHandler;

  private FundingCaseManager&MockObject $fundingCaseManagerMock;

  private FundingCaseRecipientContactSetHandlerInterface&MockObject $recipientContactSetHandlerMock;

  protected function setUp(): void {
    parent::setUp();
    $this->applicationProcessManagerMock = $this->createMock(ApplicationProcessManager::class);
    $this->recipientContactSetHandlerMock = $this->createMock(FundingCaseRecipientContactSetHandlerInterface::class);
    $this->fundingCaseManagerMock = $this->createMock(FundingCaseManager::class);

    $this->actionHandler = new SetRecipientContactActionHandler(
      $this->applicationProcessManagerMock,
      $this->fundingCaseManagerMock,
      $this->recipientContactSetHandlerMock,
    );
  }

  public function testUpdateAmountApproved(): void {
    $action = $this->createApi4ActionMock(SetRecipientContactAction::class);
    $action->setId(FundingCaseFactory::DEFAULT_ID)
      ->setContactId(1234);

    $fundingCaseBundle = FundingCaseBundleFactory::create();
    $fundingCase = $fundingCaseBundle->getFundingCase();
    $this->fundingCaseManagerMock->method('getBundle')
      ->with($fundingCase->getId())
      ->willReturn($fundingCaseBundle);

    $statusList = [22 => new FullApplicationProcessStatus('new', FALSE, FALSE)];
    $this->applicationProcessManagerMock->method('getStatusListByFundingCaseId')
      ->with($fundingCase->getId())
      ->willReturn($statusList);

    $this->recipientContactSetHandlerMock->expects(static::once())->method('handle')
      ->with(new FundingCaseRecipientContactSetCommand(
        $fundingCaseBundle,
        1234,
        $statusList,
      ));

    static::assertEquals($fundingCase->toArray(), $this->actionHandler->setRecipientContact($action));
  }

}
