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

namespace tests\phpunit\Civi\Funding\FundingCase\Handler;

use Civi\API\Exception\UnauthorizedException;
use Civi\Funding\Entity\FullApplicationProcessStatus;
use Civi\Funding\EntityFactory\FundingCaseFactory;
use Civi\Funding\EntityFactory\FundingCaseTypeFactory;
use Civi\Funding\EntityFactory\FundingProgramFactory;
use Civi\Funding\FundingCase\Actions\FundingCaseActionsDeterminerInterface;
use Civi\Funding\FundingCase\Command\FundingCaseNotificationContactsSetCommand;
use Civi\Funding\FundingCase\FundingCaseManager;
use Civi\Funding\FundingCase\Handler\FundingCaseNotificationContactsSetHandler;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\FundingCase\Handler\FundingCaseNotificationContactsSetHandler
 * @covers \Civi\Funding\FundingCase\Command\FundingCaseNotificationContactsSetCommand
 */
final class FundingCaseNotificationContactsSetHandlerTest extends TestCase {

  /**
   * @var \Civi\Funding\FundingCase\Actions\FundingCaseActionsDeterminerInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $actionsDeterminerMock;

  /**
   * @var \Civi\Funding\FundingCase\FundingCaseManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $fundingCaseManagerMock;

  private FundingCaseNotificationContactsSetHandler $handler;

  protected function setUp(): void {
    parent::setUp();
    $this->actionsDeterminerMock = $this->createMock(FundingCaseActionsDeterminerInterface::class);
    $this->fundingCaseManagerMock = $this->createMock(FundingCaseManager::class);
    $this->handler = new FundingCaseNotificationContactsSetHandler(
      $this->actionsDeterminerMock,
      $this->fundingCaseManagerMock
    );
  }

  public function testHandle(): void {
    $command = $this->createCommand();
    $this->actionsDeterminerMock->method('isActionAllowed')
      ->with(
        'set-notification-contacts',
        $command->getFundingCase()->getStatus(),
        $command->getApplicationProcessStatusList(),
        $command->getFundingCase()->getPermissions()
      )
      ->willReturn(TRUE);

    $this->fundingCaseManagerMock->expects(static::once())->method('update')
      ->with($command->getFundingCase());

    $this->handler->handle($command);
    static::assertSame([1, 2, 3, 4], $command->getFundingCase()->getNotificationContactIds());
  }

  public function testHandleUnauthorized(): void {
    $command = $this->createCommand();
    $this->actionsDeterminerMock->method('isActionAllowed')
      ->with(
        'set-notification-contacts',
        $command->getFundingCase()->getStatus(),
        $command->getApplicationProcessStatusList(),
        $command->getFundingCase()->getPermissions()
      )
      ->willReturn(FALSE);

    $this->expectException(UnauthorizedException::class);
    $this->expectExceptionMessage('Changing the notification contacts of this funding case is not allowed.');
    $this->handler->handle($command);
  }

  private function createCommand(): FundingCaseNotificationContactsSetCommand {
    $fundingCase = FundingCaseFactory::createFundingCase();
    $notificationContactIds = [1, 2, 3, 4];
    $fundingCaseType = FundingCaseTypeFactory::createFundingCaseType();
    $fundingProgram = FundingProgramFactory::createFundingProgram();

    return new FundingCaseNotificationContactsSetCommand(
      $fundingCase,
      $notificationContactIds,
      [22 => new FullApplicationProcessStatus('eligible', TRUE, TRUE)],
      $fundingCaseType,
      $fundingProgram
    );
  }

}
