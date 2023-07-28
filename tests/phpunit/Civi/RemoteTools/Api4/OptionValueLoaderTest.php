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

namespace Civi\RemoteTools\Api4;

use Civi\Api4\Generic\DAOGetAction;
use Civi\Api4\Generic\Result;
use Civi\Funding\Traits\CreateMockTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\RemoteTools\Api4\OptionValueLoader
 */
final class OptionValueLoaderTest extends TestCase {

  use CreateMockTrait;

  /**
   * @var \Civi\RemoteTools\Api4\Api4Interface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $api4Mock;

  private OptionValueLoader $optionValueLoader;

  protected function setUp(): void {
    parent::setUp();
    $this->api4Mock = $this->createMock(Api4Interface::class);
    $this->optionValueLoader = new OptionValueLoader($this->api4Mock);
  }

  public function testGetOptionValue(): void {
    $action = $this->createApi4ActionMock(DAOGetAction::class, 'OptionValue', 'get');
    $this->api4Mock->method('createGetAction')
      ->with('OptionValue')
      ->willReturn($action);

    $this->api4Mock->expects(static::once())->method('executeAction')
      ->with($action)
      ->willReturnCallback(
        function (DAOGetAction $action) {
          static::assertFalse($action->getCheckPermissions());
          static::assertSame([
            ['option_group_id:name', '=', 'group', FALSE],
            ['name', '=', 'optionName', FALSE],
          ], $action->getWhere());

          return new Result([['value' => 'optionValue']]);
        }
      );

    static::assertSame('optionValue', $this->optionValueLoader->getOptionValue('group', 'optionName'));
    // Test that value is cached.
    static::assertSame('optionValue', $this->optionValueLoader->getOptionValue('group', 'optionName'));
  }

}
