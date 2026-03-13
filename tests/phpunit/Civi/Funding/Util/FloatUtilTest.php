<?php
/*
 * Copyright (C) 2026 SYSTOPIA GmbH
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

namespace Civi\Funding\Util;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\Util\FloatUtil
 */
final class FloatUtilTest extends TestCase {

  public function testIsMoneyEqual(): void {
    static::assertTrue(FloatUtil::isNearlyEqual(1.2343, 1.2344, 3));
    static::assertFalse(FloatUtil::isNearlyEqual(1.2344, 1.2345, 3));
  }

  public function testIsNearlyEqual(): void {
    static::assertTrue(FloatUtil::isMoneyEqual(1.233, 1.234));
    static::assertFalse(FloatUtil::isMoneyEqual(1.234, 1.235));
  }

}
