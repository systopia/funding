<?php
/*
 * Copyright (C) 2022 SYSTOPIA GmbH
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

namespace Civi\Funding\FundingProgram;

use Civi\Api4\Generic\Result;
use Civi\Core\CiviEventDispatcherInterface;
use Civi\Funding\AbstractContainerMockedTestCase;
use Civi\Funding\Api4\Action\FundingProgram\GetAction;
use Civi\Funding\Entity\FundingProgramEntity;
use Civi\Funding\EntityFactory\FundingProgramFactory;
use Civi\Funding\Mock\Session\TestFundingSession;
use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\Authorization\PossiblePermissionsLoaderInterface;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @covers \Civi\Funding\FundingProgram\FundingProgramManager
 */
final class FundingProgramManagerTest extends AbstractContainerMockedTestCase {

  /**
   * @var \Civi\RemoteTools\Api4\Api4Interface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $api4Mock;

  /**
   * @var \Civi\Funding\FundingProgram\FundingProgramManager
   */
  private FundingProgramManager $fundingProgramManger;

  protected function setUp(): void {
    parent::setUp();
    $this->api4Mock = $this->createMock(Api4Interface::class);
    $this->fundingProgramManger = new FundingProgramManager($this->api4Mock);
  }

  public function testGet(): void {
    $fundingProgram = $this->createFundingProgram();

    $eventDispatcherMock = $this->createMock(CiviEventDispatcherInterface::class);
    $possiblePermissionsLoaderMock = $this->createMock(PossiblePermissionsLoaderInterface::class);
    $session = TestFundingSession::newInternal();
    $this->containerMock->expects(static::exactly(2))->method('get')->with(GetAction::class)
      ->willReturnOnConsecutiveCalls(
        new GetAction($eventDispatcherMock, $possiblePermissionsLoaderMock, $session),
        new GetAction($eventDispatcherMock, $possiblePermissionsLoaderMock, $session),

      );
    $this->api4Mock->expects(static::exactly(2))->method('executeAction')->withConsecutive(
      [
        static::callback(function (GetAction $action) {
          static::assertTrue($action->isAllowEmptyRecordPermissions());
          static::assertSame([['id', '=', 13, FALSE]], $action->getWhere());

          return TRUE;
        }),
      ],
      [
        static::callback(function (GetAction $action) {
          static::assertTrue($action->isAllowEmptyRecordPermissions());
          static::assertSame([['id', '=', 12, FALSE]], $action->getWhere());

          return TRUE;
        }),
      ]
    )->willReturnOnConsecutiveCalls(new Result(), new Result([$fundingProgram->toArray()]));

    $fundingCaseLoaded = $this->fundingProgramManger->get(13);
    static::assertNull($fundingCaseLoaded);

    $fundingCaseLoaded = $this->fundingProgramManger->get(12);
    static::assertEquals($fundingProgram, $fundingCaseLoaded);
  }

  protected function createFundingProgram(): FundingProgramEntity {
    return FundingProgramFactory::createFundingProgram();
  }

}
