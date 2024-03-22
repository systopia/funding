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

namespace Civi\Funding\Util;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\Util\ArrayUtil
 */
final class ArrayUtilTest extends TestCase {

  public function testSetValue(): void {
    $array = [];
    ArrayUtil::setValue($array, ['x', 'y', 2], 'test');
    static::assertSame(['x' => ['y' => [2 => 'test']]], $array);

    $array = ['x' => 1];
    ArrayUtil::setValue($array, ['x'], 2);
    static::assertEquals(['x' => 2], $array);

    $array = ['x' => ['y' => 'z']];
    ArrayUtil::setValue($array, ['x', 'a'], 'test');
    static::assertEquals(['x' => ['y' => 'z', 'a' => 'test']], $array);

    $array = [];
    ArrayUtil::setValue($array, [], ['x' => 'y']);
    static::assertSame(['x' => 'y'], $array);
  }

  public function testSetValueAtPointer(): void {
    $array = [];
    ArrayUtil::setValueAtPointer($array, '/x/y/2', 'test');
    static::assertSame(['x' => ['y' => [2 => 'test']]], $array);

    $array = ['x' => 1];
    ArrayUtil::setValueAtPointer($array, '/x', 2);
    static::assertEquals(['x' => 2], $array);

    $array = ['x' => ['y' => 'z']];
    ArrayUtil::setValueAtPointer($array, '/x/a', 'test');
    static::assertEquals(['x' => ['y' => 'z', 'a' => 'test']], $array);

    $array = [];
    ArrayUtil::setValueAtPointer($array, '/', ['x' => 'y']);
    static::assertSame(['x' => 'y'], $array);
  }

}
