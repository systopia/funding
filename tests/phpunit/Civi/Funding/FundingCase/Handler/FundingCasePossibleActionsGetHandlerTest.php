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

use Civi\Funding\ApplicationProcess\ApplicationProcessManager;
use Civi\Funding\EntityFactory\FundingCaseFactory;
use Civi\Funding\EntityFactory\FundingCaseTypeFactory;
use Civi\Funding\FundingCase\Command\FundingCasePossibleActionsGetCommand;
use Civi\Funding\FundingCase\FundingCaseActionsDeterminerInterface;
use Civi\RemoteTools\Api4\Query\CompositeCondition;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\FundingCase\Handler\FundingCasePossibleActionsGetHandler
 * @covers \Civi\Funding\FundingCase\Command\FundingCasePossibleActionsGetCommand
 */
final class FundingCasePossibleActionsGetHandlerTest extends TestCase {

  /**
   * @var \Civi\Funding\FundingCase\FundingCaseActionsDeterminerInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $actionsDeterminerMock;

  /**
   * @var \Civi\Funding\ApplicationProcess\ApplicationProcessManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $applicationProcessManagerMock;

  private FundingCasePossibleActionsGetHandler $handler;

  protected function setUp(): void {
    parent::setUp();
    $this->applicationProcessManagerMock = $this->createMock(ApplicationProcessManager::class);
    $this->actionsDeterminerMock = $this->createMock(FundingCaseActionsDeterminerInterface::class);
    $this->handler = new FundingCasePossibleActionsGetHandler(
      $this->applicationProcessManagerMock,
      $this->actionsDeterminerMock,
    );
  }

  public function testHandle(): void {
    $command = $this->createCommand();
    $fundingCase = $command->getFundingCase();
    $this->actionsDeterminerMock->method('getActions')
      ->with($fundingCase->getStatus(), $fundingCase->getPermissions())
      ->willReturn(['permitted_action']);

    static::assertSame(['permitted_action'], $this->handler->handle($command));
  }

  public function testHandleApprove(): void {
    $command = $this->createCommand();
    $fundingCase = $command->getFundingCase();
    $this->actionsDeterminerMock->method('getActions')
      ->with($fundingCase->getStatus(), $fundingCase->getPermissions())
      ->willReturn(['approve']);

    $this->applicationProcessManagerMock->expects(static::exactly(2))->method('countBy')
      ->withConsecutive(
        [CompositeCondition::fromFieldValuePairs(['funding_case_id' => $fundingCase->getId(), 'is_eligible' => NULL])],
        [CompositeCondition::fromFieldValuePairs(['funding_case_id' => $fundingCase->getId(), 'is_eligible' => TRUE])],
      )->willReturnOnConsecutiveCalls(0, 1);

    static::assertSame(['approve'], $this->handler->handle($command));
  }

  public function testHandleApproveNoEligibleApplication(): void {
    $command = $this->createCommand();
    $fundingCase = $command->getFundingCase();
    $this->actionsDeterminerMock->method('getActions')
      ->with($fundingCase->getStatus(), $fundingCase->getPermissions())
      ->willReturn(['approve']);

    $this->applicationProcessManagerMock->expects(static::exactly(2))->method('countBy')
      ->withConsecutive(
        [CompositeCondition::fromFieldValuePairs(['funding_case_id' => $fundingCase->getId(), 'is_eligible' => NULL])],
        [CompositeCondition::fromFieldValuePairs(['funding_case_id' => $fundingCase->getId(), 'is_eligible' => TRUE])],
      )->willReturnOnConsecutiveCalls(0, 0);

    static::assertSame([], $this->handler->handle($command));
  }

  private function createCommand(): FundingCasePossibleActionsGetCommand {
    $fundingCase = FundingCaseFactory::createFundingCase();
    $fundingCaseType = FundingCaseTypeFactory::createFundingCaseType();

    return new FundingCasePossibleActionsGetCommand($fundingCase, $fundingCaseType);
  }

}
