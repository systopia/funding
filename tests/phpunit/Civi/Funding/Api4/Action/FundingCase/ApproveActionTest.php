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
use Civi\Funding\EntityFactory\FundingCaseFactory;
use Civi\Funding\EntityFactory\FundingCaseTypeFactory;
use Civi\Funding\EntityFactory\FundingProgramFactory;
use Civi\Funding\FundingCase\Command\FundingCaseApproveCommand;
use Civi\Funding\FundingCase\FundingCaseManager;
use Civi\Funding\FundingCase\Handler\FundingCaseApproveHandlerInterface;
use Civi\Funding\FundingCase\TransferContractRouter;
use Civi\Funding\FundingProgram\FundingCaseTypeManager;
use Civi\Funding\FundingProgram\FundingProgramManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\Api4\Action\FundingCase\ApproveAction
 */
final class ApproveActionTest extends TestCase {

  private ApproveAction $action;

  /**
   * @var \Civi\Funding\FundingCase\Handler\FundingCaseApproveHandlerInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $approveHandlerMock;

  /**
   * @var \Civi\Funding\FundingCase\FundingCaseManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $fundingCaseManagerMock;

  /**
   * @var \Civi\Funding\FundingProgram\FundingCaseTypeManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $fundingCaseTypeManagerMock;

  /**
   * @var \Civi\Funding\FundingProgram\FundingProgramManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $fundingProgramManagerMock;

  /**
   * @var \Civi\Funding\FundingCase\TransferContractRouter&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $transferContractRouterMock;

  protected function setUp(): void {
    parent::setUp();
    $this->approveHandlerMock = $this->createMock(FundingCaseApproveHandlerInterface::class);
    $this->fundingCaseManagerMock = $this->createMock(FundingCaseManager::class);
    $this->fundingCaseTypeManagerMock = $this->createMock(FundingCaseTypeManager::class);
    $this->fundingProgramManagerMock = $this->createMock(FundingProgramManager::class);
    $this->transferContractRouterMock = $this->createMock(TransferContractRouter::class);
    $this->action = new ApproveAction(
      $this->approveHandlerMock,
      $this->fundingCaseManagerMock,
      $this->fundingCaseTypeManagerMock,
      $this->fundingProgramManagerMock,
      $this->transferContractRouterMock,
    );
  }

  public function test(): void {
    $this->action
      ->setId(FundingCaseFactory::DEFAULT_ID)
      ->setTitle('title')
      ->setAmount(12.34);

    $fundingCase = FundingCaseFactory::createFundingCase();
    $this->fundingCaseManagerMock->method('get')
      ->with(FundingCaseFactory::DEFAULT_ID)
      ->willReturn($fundingCase);

    $fundingCaseType = FundingCaseTypeFactory::createFundingCaseType();
    $this->fundingCaseTypeManagerMock->method('get')
      ->with($fundingCase->getFundingCaseTypeId())
      ->willReturn($fundingCaseType);

    $fundingProgram = FundingProgramFactory::createFundingProgram();
    $this->fundingProgramManagerMock->method('get')
      ->with($fundingCase->getFundingProgramId())
      ->willReturn($fundingProgram);

    $this->approveHandlerMock->method('handle')
      ->with(static::callback(function (FundingCaseApproveCommand $command)
        use ($fundingCase, $fundingCaseType, $fundingProgram) {
        static::assertSame('title', $command->getTitle());
        static::assertSame(12.34, $command->getAmount());
        static::assertSame($fundingCase, $command->getFundingCase());
        static::assertSame($fundingCaseType, $command->getFundingCaseType());
        static::assertSame($fundingProgram, $command->getFundingProgram());

        return TRUE;
      }));

    $this->transferContractRouterMock->method('generate')
      ->with(FundingCaseFactory::DEFAULT_ID)
      ->willReturn('http://example.org/transfer-contract');

    $result = new Result();
    $this->action->_run($result);
    $fundingCase->setTransferContractUri('http://example.org/transfer-contract');
    static::assertEquals($fundingCase->toArray(), $result->getArrayCopy());
  }

}
