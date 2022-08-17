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
 * @covers \Civi\RemoteTools\Api4\Query\WhereParameter
 */
final class WhereParameterTest extends TestCase {

  public function test(): void {
    $conditionA = Comparison::new('fieldA', '=', 'valueA');
    $conditionB = Comparison::new('fieldB', '!=', 123);
    $whereParameter = WhereParameter::new($conditionA, $conditionB);

    static::assertSame([$conditionA, $conditionB], $whereParameter->getConditions());
    static::assertSame([$conditionA->toArray(), $conditionB->toArray()], $whereParameter->toParam());
  }

  public function testEmpty(): void {
    $whereParameter = WhereParameter::new();
    static::assertSame([], $whereParameter->getConditions());
    static::assertNull($whereParameter->toParam());
  }

}
