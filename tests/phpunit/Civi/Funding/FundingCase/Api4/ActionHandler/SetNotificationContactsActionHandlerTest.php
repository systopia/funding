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

use Civi\Funding\Api4\Action\FundingCase\SetNotificationContactsAction;
use Civi\Funding\ApplicationProcess\ApplicationProcessManager;
use Civi\Funding\Entity\FullApplicationProcessStatus;
use Civi\Funding\EntityFactory\FundingCaseBundleFactory;
use Civi\Funding\EntityFactory\FundingCaseFactory;
use Civi\Funding\FundingCase\Command\FundingCaseNotificationContactsSetCommand;
use Civi\Funding\FundingCase\FundingCaseManager;
use Civi\Funding\FundingCase\Handler\FundingCaseNotificationContactsSetHandlerInterface;
use Civi\Funding\Traits\CreateMockTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\FundingCase\Api4\ActionHandler\SetNotificationContactsActionHandler
 */
final class SetNotificationContactsActionHandlerTest extends TestCase {

  use CreateMockTrait;

  private ApplicationProcessManager&MockObject $applicationProcessManagerMock;

  private SetNotificationContactsActionHandler $actionHandler;

  private FundingCaseManager&MockObject $fundingCaseManagerMock;

  private FundingCaseNotificationContactsSetHandlerInterface&MockObject $notificationContactsSetHandlerMock;

  protected function setUp(): void {
    parent::setUp();
    $this->applicationProcessManagerMock = $this->createMock(ApplicationProcessManager::class);
    $this->notificationContactsSetHandlerMock = $this->createMock(
      FundingCaseNotificationContactsSetHandlerInterface::class
    );
    $this->fundingCaseManagerMock = $this->createMock(FundingCaseManager::class);

    $this->actionHandler = new SetNotificationContactsActionHandler(
      $this->applicationProcessManagerMock,
      $this->fundingCaseManagerMock,
      $this->notificationContactsSetHandlerMock,
    );
  }

  public function testUpdateAmountApproved(): void {
    $action = $this->createApi4ActionMock(SetNotificationContactsAction::class);
    $action->setId(FundingCaseFactory::DEFAULT_ID)
      ->setContactIds([1, 2, 3, 4]);

    $fundingCaseBundle = FundingCaseBundleFactory::create();
    $fundingCase = $fundingCaseBundle->getFundingCase();
    $this->fundingCaseManagerMock->method('getBundle')
      ->with($fundingCase->getId())
      ->willReturn($fundingCaseBundle);

    $statusList = [22 => new FullApplicationProcessStatus('new', FALSE, FALSE)];
    $this->applicationProcessManagerMock->method('getStatusListByFundingCaseId')
      ->with($fundingCase->getId())
      ->willReturn($statusList);

    $this->notificationContactsSetHandlerMock->expects(static::once())->method('handle')
      ->with(new FundingCaseNotificationContactsSetCommand(
        $fundingCaseBundle,
        [1, 2, 3, 4],
        $statusList,
      ));

    static::assertEquals($fundingCase->toArray(), $this->actionHandler->setNotificationContacts($action));
  }

}
