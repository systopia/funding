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

/**
 * @noinspection PhpUnhandledExceptionInspection
 */

declare(strict_types = 1);

namespace Civi\Funding\Api4\Action\FundingCaseTypeProgram;

use Civi\Api4\Generic\BasicGetAction;
use Civi\Api4\Generic\Result;
use Civi\Funding\Traits\CreateMockTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\Api4\Action\FundingCaseTypeProgram\GetRelationAction
 */
final class GetRelationActionTest extends TestCase {

  use CreateMockTrait;

  /**
   * @var \Civi\Api4\Generic\BasicGetAction&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $getActionMock;

  private GetRelationAction $action;

  protected function setUp(): void {
    parent::setUp();
    $this->getActionMock = $this->createMockWithExtraMethods(BasicGetAction::class, ['setDebug']);
    $this->getActionMock->method('getEntityName')->willReturn('TestEntity');
    $this->action = $this->createApi4ActionMock(GetRelationAction::class, $this->getActionMock);
  }

  public function testRun(): void {
    static::assertSame('TestEntity', $this->action->getEntityName());
    static::assertSame('getRelation', $this->action->getActionName());

    $this->action
      ->setDebug(TRUE)
      ->setCheckPermissions(TRUE)
      ->setFundingCaseTypeId(22)
      ->setFundingProgramId(33);

    $this->getActionMock->expects(static::once())->method('setCheckPermissions')->with(TRUE)
      ->willReturnSelf();
    $this->getActionMock->expects(static::once())->method('setDebug')->with(TRUE)
      ->willReturnSelf();
    $this->getActionMock->expects(static::exactly(2))->method('addWhere')->withConsecutive(
      ['funding_case_type_id', '=', 22],
      ['funding_program_id', '=', 33]
    )->willReturnSelf();

    $result = new Result();
    $this->getActionMock->expects(static::once())->method('_run')->with($result)
      ->willReturnCallback(function (Result $result) {
        $result->exchangeArray([['foo' => 'bar']]);
        $this->getActionMock->_debugOutput = ['debug' => 'test'];
      });

    $this->action->_run($result);
    static::assertSame([['foo' => 'bar']], $result->getArrayCopy());
    static::assertSame(['debug' => 'test'], $this->action->_debugOutput);
  }

}
