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
use Civi\Funding\Api4\Action\FundingCaseTypeProgram\GetRelationAction;
use Civi\RemoteTools\Api4\Api4Interface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\FundingProgram\FundingCaseTypeProgramRelationChecker
 */
final class FundingCaseTypeProgramRelationCheckerTest extends TestCase {

  /**
   * @var \Civi\RemoteTools\Api4\Api4Interface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $api4Mock;

  private FundingCaseTypeProgramRelationChecker $relationChecker;

  protected function setUp(): void {
    parent::setUp();
    $this->api4Mock = $this->createMock(Api4Interface::class);
    $this->relationChecker = new FundingCaseTypeProgramRelationChecker($this->api4Mock);
  }

  public function testAreFundingCaseTypeAndProgramRelatedTrue(): void {
    $this->api4Mock->expects(static::once())->method('executeAction')
      ->with(static::isInstanceOf(GetRelationAction::class))
      ->willReturnCallback(function (GetRelationAction $action) {
        static::assertSame(22, $action->getFundingCaseTypeId());
        static::assertSame(33, $action->getFundingProgramId());

        $result = new Result();
        $result->rowCount = 1;

        return $result;
      });

    static::assertTrue($this->relationChecker->areFundingCaseTypeAndProgramRelated(22, 33));
  }

  public function testAreFundingCaseTypeAndProgramRelatedFalse(): void {
    $this->api4Mock->expects(static::once())->method('executeAction')
      ->with(static::isInstanceOf(GetRelationAction::class))
      ->willReturnCallback(function (GetRelationAction $action) {
        static::assertSame(22, $action->getFundingCaseTypeId());
        static::assertSame(33, $action->getFundingProgramId());

        $result = new Result();
        $result->rowCount = 0;

        return $result;
      });

    static::assertFalse($this->relationChecker->areFundingCaseTypeAndProgramRelated(22, 33));
  }

}
