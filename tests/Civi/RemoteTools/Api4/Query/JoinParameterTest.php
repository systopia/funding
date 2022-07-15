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

namespace Civi\RemoteTools\Api4\Query;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\RemoteTools\Api4\Query\JoinParameter
 */
final class JoinParameterTest extends TestCase {

  public function test(): void {
    $joinA = Join::newWithBridge('Foo', 'f', 'LEFT', 'bridgeA');
    $joinB = Join::newWithBridge('Foo', 'f', 'LEFT', 'bridgeB');
    $joinParameter = JoinParameter::new($joinA, $joinB);

    static::assertSame([$joinA, $joinB], $joinParameter->getJoins());
    static::assertSame([$joinA->toArray(), $joinB->toArray()], $joinParameter->toParam());
  }

  public function testEmpty(): void {
    $joinParameter = JoinParameter::new();
    static::assertSame([], $joinParameter->getJoins());
    static::assertNull($joinParameter->toParam());
  }

}
