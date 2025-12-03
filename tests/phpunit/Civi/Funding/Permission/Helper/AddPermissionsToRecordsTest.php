<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
 *
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License as published by the Free
 * Software Foundation, either version 3 of the License, or (at your option) any
 * later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types = 1);

namespace Civi\Funding\Permission\Helper;

use Civi\Api4\Generic\Result;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\Permission\Helper\AddPermissionsToRecords
 */
final class AddPermissionsToRecordsTest extends TestCase {

  public function testPermissionIsAdded(): void {
    $addPermissionsToRecords = new AddPermissionsToRecords(
      ['foo', 'bar', 'baz'],
      fn () => ['foo', 'bar']
    );
    $result = new Result([['id' => 1, 'name' => 'Test']]);
    $addPermissionsToRecords($result);

    static::assertSame(1, $result->countFetched());
    $expectedRecord = [
      'id' => 1,
      'name' => 'Test',
      'permissions' => ['foo', 'bar'],
      'PERM_foo' => TRUE,
      'PERM_bar' => TRUE,
      'PERM_baz' => FALSE,
    ];
    static::assertSame([$expectedRecord], $result->getArrayCopy());

  }

  public function testRecordIsFilteredOut(): void {

    $addPermissionsToRecords = new AddPermissionsToRecords(['foo'], fn () => []);
    $result = new Result([['id' => 1, 'name' => 'Test']]);
    $result->setCountMatched(2);
    $addPermissionsToRecords($result);

    static::assertSame(0, $result->countFetched());
    static::assertSame(1, $result->countMatched());
    static::assertSame([], $result->getArrayCopy());

  }

}
