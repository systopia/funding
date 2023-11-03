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

namespace Civi\Funding\FundingProgram\Api4\ActionHandler;

use Civi\API\Exception\UnauthorizedException;
use Civi\Funding\Api4\Action\FundingProgram\GetAmountApprovedAction;
use Civi\Funding\EntityFactory\FundingCaseFactory;
use Civi\Funding\EntityFactory\FundingProgramFactory;
use Civi\Funding\FundingCase\FundingCaseManager;
use Civi\Funding\FundingProgram\FundingProgramManager;
use Civi\Funding\Traits\CreateMockTrait;
use Civi\RemoteTools\Api4\Query\Comparison;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\FundingProgram\Api4\ActionHandler\GetAmountApprovedHandler
 */
final class GetAmountApprovedHandlerTest extends TestCase {

  use CreateMockTrait;

  /**
   * @var \Civi\Funding\FundingCase\FundingCaseManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $fundingCaseManagerMock;

  /**
   * @var \Civi\Funding\FundingProgram\FundingProgramManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $fundingProgramManagerMock;

  private GetAmountApprovedHandler $handler;

  protected function setUp(): void {
    parent::setUp();

    $this->fundingCaseManagerMock = $this->createMock(FundingCaseManager::class);
    $this->fundingProgramManagerMock = $this->createMock(FundingProgramManager::class);
    $this->handler = new GetAmountApprovedHandler(
      $this->fundingCaseManagerMock,
      $this->fundingProgramManagerMock
    );
  }

  public function testWithFundingProgramAccess(): void {
    $fundingProgram = FundingProgramFactory::createFundingProgram();
    $this->fundingProgramManagerMock->expects(static::once())->method('getIfAllowed')
      ->with($fundingProgram->getId())
      ->willReturn($fundingProgram);
    $this->fundingProgramManagerMock->method('getAmountApproved')
      ->with($fundingProgram->getId())
      ->willReturn(12.0);

    $action = $this->createApi4ActionMock(GetAmountApprovedAction::class)
      ->setId($fundingProgram->getId());

    static::assertSame([12.0], $this->handler->getAmountApproved($action));
  }

  public function testWithFundingCaseAccess(): void {
    $fundingProgram = FundingProgramFactory::createFundingProgram();
    $this->fundingCaseManagerMock->expects(static::once())->method('getFirstBy')
      ->with(Comparison::new('funding_program_id', '=', $fundingProgram->getId()))
      ->willReturn(FundingCaseFactory::createFundingCase());
    $this->fundingProgramManagerMock->method('getAmountApproved')
      ->with($fundingProgram->getId())
      ->willReturn(12.0);

    $action = $this->createApi4ActionMock(GetAmountApprovedAction::class)
      ->setId($fundingProgram->getId());

    static::assertSame([12.0], $this->handler->getAmountApproved($action));
  }

  public function testUnauthorized(): void {
    $this->fundingProgramManagerMock->expects(static::never())->method('getAmountApproved');
    static::expectException(UnauthorizedException::class);

    $action = $this->createApi4ActionMock(GetAmountApprovedAction::class)
      ->setId(123);

    static::assertSame([12.0], $this->handler->getAmountApproved($action));
  }

}
