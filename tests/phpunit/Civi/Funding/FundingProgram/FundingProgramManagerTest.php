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

use Civi\Api4\FundingProgram;
use Civi\Api4\Generic\Result;
use Civi\Funding\Api4\Action\FundingProgram\GetAction;
use Civi\Funding\Entity\FundingProgramEntity;
use Civi\Funding\EntityFactory\FundingProgramFactory;
use Civi\RemoteTools\Api4\Api4Interface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\FundingProgram\FundingProgramManager
 */
final class FundingProgramManagerTest extends TestCase {

  private Api4Interface&MockObject $api4Mock;

  private FundingProgramManager $fundingProgramManger;

  protected function setUp(): void {
    parent::setUp();
    $this->api4Mock = $this->createMock(Api4Interface::class);
    $this->fundingProgramManger = new FundingProgramManager($this->api4Mock);
  }

  public function testGet(): void {
    $fundingProgram = $this->createFundingProgram();

    $this->api4Mock->expects(static::exactly(2))->method('executeAction')
      ->willReturnCallback(function (GetAction $action) use ($fundingProgram) {
        static $calls = 0;

        static::assertTrue($action->isAllowEmptyRecordPermissions());
        if (0 === $calls++) {
          static::assertSame([['id', '=', 13, FALSE]], $action->getWhere());

          return new Result();
        }
        else {
          static::assertSame([['id', '=', 12, FALSE]], $action->getWhere());

          return new Result([$fundingProgram->toArray()]);
        }
      });

    $fundingProgramLoaded = $this->fundingProgramManger->get(13);
    static::assertNull($fundingProgramLoaded);

    $fundingProgramLoaded = $this->fundingProgramManger->get(12);
    static::assertEquals($fundingProgram, $fundingProgramLoaded);
  }

  public function testGetIfAllowed(): void {
    $fundingProgram = FundingProgramFactory::createFundingProgram();
    $this->api4Mock->method('getEntity')->with(FundingProgram::getEntityName(), $fundingProgram->getId())
      ->willReturn($fundingProgram->toArray());

    static::assertEquals($fundingProgram, $this->fundingProgramManger->getIfAllowed($fundingProgram->getId()));
  }

  public function testGetIfAllowedNull(): void {
    static::assertNull($this->fundingProgramManger->getIfAllowed(123));
  }

  public function testGetAmountApproved(): void {
    $this->api4Mock->expects(static::once())->method('executeAction')
      ->willReturnCallback(function (GetAction $action) {
        static::assertSame(['amount_approved'], $action->getSelect());
        static::assertSame([['id', '=', 12, FALSE]], $action->getWhere());
        static::assertTrue($action->isAllowEmptyRecordPermissions());

        return new Result([['amount_approved' => 123]]);
      });

    static::assertSame(123.0, $this->fundingProgramManger->getAmountApproved(12));
  }

  protected function createFundingProgram(): FundingProgramEntity {
    return FundingProgramFactory::createFundingProgram();
  }

}
