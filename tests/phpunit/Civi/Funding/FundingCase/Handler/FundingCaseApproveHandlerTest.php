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

namespace Civi\Funding\FundingCase\Handler;

use Civi\API\Exception\UnauthorizedException;
use Civi\Funding\Entity\FullApplicationProcessStatus;
use Civi\Funding\EntityFactory\FundingCaseFactory;
use Civi\Funding\EntityFactory\FundingCaseTypeFactory;
use Civi\Funding\EntityFactory\FundingProgramFactory;
use Civi\Funding\FundingCase\Actions\FundingCaseActionsDeterminerInterface;
use Civi\Funding\FundingCase\Command\FundingCaseApproveCommand;
use Civi\Funding\FundingCase\FundingCaseManager;
use Civi\Funding\FundingCase\StatusDeterminer\FundingCaseStatusDeterminerInterface;
use Civi\Funding\TransferContract\TransferContractCreator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\FundingCase\Handler\FundingCaseApproveHandler
 * @covers \Civi\Funding\FundingCase\Command\FundingCaseApproveCommand
 */
final class FundingCaseApproveHandlerTest extends TestCase {

  /**
   * @var \Civi\Funding\FundingCase\Actions\FundingCaseActionsDeterminerInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $actionsDeterminerMock;

  /**
   * @var \Civi\Funding\FundingCase\FundingCaseManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $fundingCaseManagerMock;

  private FundingCaseApproveHandler $handler;

  /**
   * @var \Civi\Funding\FundingCase\StatusDeterminer\FundingCaseStatusDeterminerInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $statusDeterminerMock;

  /**
   * @var \Civi\Funding\TransferContract\TransferContractCreator&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $transferContractCreatorMock;

  protected function setUp(): void {
    parent::setUp();
    $this->actionsDeterminerMock = $this->createMock(FundingCaseActionsDeterminerInterface::class);
    $this->fundingCaseManagerMock = $this->createMock(FundingCaseManager::class);
    $this->statusDeterminerMock = $this->createMock(FundingCaseStatusDeterminerInterface::class);
    $this->transferContractCreatorMock = $this->createMock(TransferContractCreator::class);
    $this->handler = new FundingCaseApproveHandler(
      $this->actionsDeterminerMock,
      $this->fundingCaseManagerMock,
      $this->statusDeterminerMock,
      $this->transferContractCreatorMock,
    );
  }

  public function testHandle(): void {
    $command = $this->createCommand();
    $this->actionsDeterminerMock->method('isActionAllowed')
      ->with(
        'approve',
        $command->getFundingCase()->getStatus(),
        $command->getApplicationProcessStatusList(),
        $command->getFundingCase()->getPermissions()
      )
      ->willReturn(TRUE);

    $this->transferContractCreatorMock->expects(static::once())->method('createTransferContract')
      ->with(
        $command->getFundingCase(),
        $command->getFundingCaseType(),
        $command->getFundingProgram(),
      );

    $this->statusDeterminerMock->method('getStatus')
      ->with($command->getFundingCase()->getStatus(), 'approve')
      ->willReturn('new_status');

    $this->fundingCaseManagerMock->expects(static::once())->method('update')
      ->with($command->getFundingCase());

    $this->handler->handle($command);
    static::assertSame('new_status', $command->getFundingCase()->getStatus());
    static::assertSame(12.34, $command->getFundingCase()->getAmountApproved());
  }

  public function testHandleUnauthorized(): void {
    $command = $this->createCommand();
    $this->actionsDeterminerMock->method('isActionAllowed')
      ->with(
        'approve',
        $command->getFundingCase()->getStatus(),
        $command->getApplicationProcessStatusList(),
        $command->getFundingCase()->getPermissions()
      )
      ->willReturn(FALSE);

    $this->expectException(UnauthorizedException::class);
    $this->expectExceptionMessage('Approving this funding case is not allowed.');
    $this->handler->handle($command);
  }

  private function createCommand(): FundingCaseApproveCommand {
    $fundingCase = FundingCaseFactory::createFundingCase();
    $amount = 12.34;
    $fundingCaseType = FundingCaseTypeFactory::createFundingCaseType();
    $fundingProgram = FundingProgramFactory::createFundingProgram();

    return new FundingCaseApproveCommand(
      $fundingCase,
      $amount,
      [22 => new FullApplicationProcessStatus('eligible', TRUE, TRUE)],
      $fundingCaseType,
      $fundingProgram
    );
  }

}
