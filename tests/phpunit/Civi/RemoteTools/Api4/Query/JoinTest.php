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
 * @covers \Civi\RemoteTools\Api4\Query\Join
 */
final class JoinTest extends TestCase {

  public function testWithCondition(): void {
    $condition = Comparison::new('field', '=', 'value');
    $join = Join::new('Foo', 'f', 'INNER', $condition);

    static::assertSame('Foo', $join->getEntityName());
    static::assertSame('f', $join->getAlias());
    static::assertSame('INNER', $join->getType());
    static::assertNull($join->getBridge());
    static::assertSame($condition, $join->getCondition());
    static::assertSame(['Foo AS f', 'INNER', $condition->toArray()], $join->toArray());
  }

  public function testWithBridge(): void {
    $join = Join::newWithBridge('Foo', 'f', 'INNER', 'bridge');

    static::assertSame('Foo', $join->getEntityName());
    static::assertSame('f', $join->getAlias());
    static::assertSame('INNER', $join->getType());
    static::assertSame('bridge', $join->getBridge());
    static::assertNull($join->getCondition());
    static::assertSame(['Foo AS f', 'INNER', 'bridge'], $join->toArray());
  }

  public function testWithBridgeAndCondition(): void {
    $condition = Comparison::new('field', '=', 'value');
    $join = Join::newWithBridge('Foo', 'f', 'INNER', 'bridge', $condition);

    static::assertSame('Foo', $join->getEntityName());
    static::assertSame('f', $join->getAlias());
    static::assertSame('INNER', $join->getType());
    static::assertSame('bridge', $join->getBridge());
    static::assertSame($condition, $join->getCondition());
    static::assertSame(['Foo AS f', 'INNER', 'bridge', $condition->toArray()], $join->toArray());
  }

}
