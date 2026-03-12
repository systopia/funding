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
use Civi\Funding\EntityFactory\FundingCaseBundleFactory;
use Civi\Funding\FundingCase\Actions\FundingCaseActionsDeterminerInterface;
use Civi\Funding\FundingCase\Command\TransferContractRecreateCommand;
use Civi\Funding\TransferContract\TransferContractCreator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\FundingCase\Handler\TransferContractRecreateHandler
 * @covers \Civi\Funding\FundingCase\Command\TransferContractRecreateCommand
 */
final class TransferContractRecreateHandlerTest extends TestCase {

  private FundingCaseActionsDeterminerInterface&MockObject $actionsDeterminerMock;

  private TransferContractRecreateHandler $handler;

  private TransferContractCreator&MockObject $transferContractCreatorMock;

  protected function setUp(): void {
    parent::setUp();
    $this->actionsDeterminerMock = $this->createMock(FundingCaseActionsDeterminerInterface::class);
    $this->transferContractCreatorMock = $this->createMock(TransferContractCreator::class);
    $this->handler = new TransferContractRecreateHandler(
      $this->actionsDeterminerMock,
      $this->transferContractCreatorMock,
    );
  }

  public function testHandle(): void {
    $command = $this->createCommand();
    $this->actionsDeterminerMock->method('isActionAllowed')
      ->with(
        'recreate-transfer-contract',
        $command->getFundingCaseBundle(),
        $command->getApplicationProcessStatusList(),
      )->willReturn(TRUE);

    $this->transferContractCreatorMock->expects(static::once())->method('createTransferContract')
      ->with($command->getFundingCaseBundle());

    $this->handler->handle($command);
  }

  public function testHandleUnauthorized(): void {
    $command = $this->createCommand();
    $this->actionsDeterminerMock->method('isActionAllowed')
      ->with(
        'recreate-transfer-contract',
        $command->getFundingCaseBundle(),
        $command->getApplicationProcessStatusList(),
      )->willReturn(FALSE);

    $this->expectException(UnauthorizedException::class);
    $this->expectExceptionMessage('Permission to recreate transfer contract is missing.');
    $this->handler->handle($command);
  }

  private function createCommand(float $amountApproved = 12.34): TransferContractRecreateCommand {
    $fundingCaseBundle = FundingCaseBundleFactory::create(['amount_approved' => $amountApproved]);
    $statusList = [22 => new FullApplicationProcessStatus('eligible', TRUE, TRUE)];

    return new TransferContractRecreateCommand($fundingCaseBundle, $statusList);
  }

}
