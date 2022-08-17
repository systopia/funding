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

namespace Civi\Funding\Api4\Action\Helper;

use Civi\Api4\Generic\Result;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\Api4\Action\Helper\AddPermissionsToRecords
 */
final class AddPermissionsToRecordsTest extends TestCase {

  public function testPermissionIsAdded(): void {

    $addPermissionsToRecords = new AddPermissionsToRecords(fn () => ['foo', 'bar']);
    $result = new Result([['id' => 1, 'name' => 'Test']]);
    $addPermissionsToRecords($result);

    static::assertSame(1, $result->rowCount);
    $expectedRecord = [
      'id' => 1,
      'name' => 'Test',
      'permissions' => ['foo', 'bar'],
      'PERM_foo' => TRUE,
      'PERM_bar' => TRUE,
    ];
    static::assertSame([$expectedRecord], $result->getArrayCopy());

  }

  public function testRecordIsFilteredOut(): void {

    $addPermissionsToRecords = new AddPermissionsToRecords(fn () => NULL);
    $result = new Result([['id' => 1, 'name' => 'Test']]);
    $addPermissionsToRecords($result);

    static::assertSame(0, $result->rowCount);
    static::assertSame([], $result->getArrayCopy());

  }

}
