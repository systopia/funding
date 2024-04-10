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

use Civi\Api4\FundingCaseType;
use Civi\Api4\Generic\Result;
use Civi\RemoteTools\Api4\Api4Interface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\FundingProgram\FundingCaseTypeManager
 */
final class FundingCaseTypeManagerTest extends TestCase {

  /**
   * @var \Civi\RemoteTools\Api4\Api4Interface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $api4Mock;

  private FundingCaseTypeManager $fundingCaseTypeManager;

  protected function setUp(): void {
    parent::setUp();
    $this->api4Mock = $this->createMock(Api4Interface::class);
    $this->fundingCaseTypeManager = new FundingCaseTypeManager($this->api4Mock);
  }

  public function testGetIdByName(): void {
    $this->api4Mock->expects(static::once())->method('execute')
      ->with(FundingCaseType::getEntityName(), 'get', [
        'select' => ['id'],
        'where' => [['name', '=', 'test']],
      ])->willReturn(new Result([['id' => 11]]));

    static::assertSame(11, $this->fundingCaseTypeManager->getIdByName('test'));
    // Cache is used
    static::assertSame(11, $this->fundingCaseTypeManager->getIdByName('test'));
  }

  public function testGetIdByNameNotFound(): void {
    $this->api4Mock->expects(static::once())->method('execute')
      ->with(FundingCaseType::getEntityName(), 'get', [
        'select' => ['id'],
        'where' => [['name', '=', 'test']],
      ])->willReturn(new Result([]));

    static::assertNull($this->fundingCaseTypeManager->getIdByName('test'));
    // Cache is used
    static::assertNull($this->fundingCaseTypeManager->getIdByName('test'));
  }

}
