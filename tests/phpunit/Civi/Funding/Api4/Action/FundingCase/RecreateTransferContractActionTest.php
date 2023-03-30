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
use Civi\Funding\FundingCase\Command\TransferContractRecreateCommand;
use Civi\Funding\FundingCase\FundingCaseManager;
use Civi\Funding\FundingCase\Handler\TransferContractRecreateHandlerInterface;
use Civi\Funding\FundingProgram\FundingCaseTypeManager;
use Civi\Funding\FundingProgram\FundingProgramManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\Api4\Action\FundingCase\RecreateTransferContractAction
 */
final class RecreateTransferContractActionTest extends TestCase {

  private RecreateTransferContractAction $action;

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
   * @var \Civi\Funding\FundingCase\Handler\TransferContractRecreateHandlerInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $transferContractRecreateHandlerMock;

  protected function setUp(): void {
    parent::setUp();
    parent::setUp();
    $this->fundingCaseManagerMock = $this->createMock(FundingCaseManager::class);
    $this->fundingCaseTypeManagerMock = $this->createMock(FundingCaseTypeManager::class);
    $this->fundingProgramManagerMock = $this->createMock(FundingProgramManager::class);
    $this->transferContractRecreateHandlerMock = $this->createMock(TransferContractRecreateHandlerInterface::class);
    $this->action = new RecreateTransferContractAction(
      $this->fundingCaseManagerMock,
      $this->fundingCaseTypeManagerMock,
      $this->fundingProgramManagerMock,
      $this->transferContractRecreateHandlerMock,
    );
  }

  public function test(): void {
    $this->action->setId(FundingCaseFactory::DEFAULT_ID);

    $fundingCase = FundingCaseFactory::createFundingCase(['amount_approved' => 12.34]);
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

    $this->transferContractRecreateHandlerMock->expects(static::once())->method('handle')
      ->with(new TransferContractRecreateCommand($fundingCase, $fundingCaseType, $fundingProgram));

    $result = new Result();
    $this->action->_run($result);
    static::assertEquals($fundingCase->toArray(), $result->getArrayCopy());
  }

}
