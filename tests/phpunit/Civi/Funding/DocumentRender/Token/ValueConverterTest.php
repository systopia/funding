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

namespace Civi\Funding\DocumentRender\Token;

use Brick\Math\BigDecimal;
use Brick\Money\Currency;
use Brick\Money\Money;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\DocumentRender\Token\ValueConverter
 */
final class ValueConverterTest extends TestCase {

  public function testToResolvedToken(): void {
    static::assertEquals(
      new ResolvedToken('foo', 'text/plain'),
      ValueConverter::toResolvedToken('foo')
    );

    static::assertEquals(
      new ResolvedToken('1.2', 'text/plain'),
      ValueConverter::toResolvedToken(1.2)
    );

    static::assertEquals(
      new ResolvedToken('1', 'text/plain'),
      ValueConverter::toResolvedToken(TRUE)
    );

    static::assertEquals(
      new ResolvedToken('0', 'text/plain'),
      ValueConverter::toResolvedToken(FALSE)
    );

    static::assertEquals(
      new ResolvedToken('', 'text/plain'),
      ValueConverter::toResolvedToken(NULL)
    );

    static::assertEquals(
      new ResolvedToken(new \DateTime('1234-05-06 07:08:09'), 'text/plain'),
      ValueConverter::toResolvedToken(new \DateTime('1234-05-06 07:08:09'))
    );

    static::assertEquals(
      new ResolvedToken(
        "    - foo<br />\n        - bar<br />\n        - baz<br />\n",
        'text/html'),
      ValueConverter::toResolvedToken(['foo', ['bar', 'baz']])
    );

    static::assertEquals(
      new ResolvedToken('', 'text/plain'),
      ValueConverter::toResolvedToken(fopen(__FILE__, 'r'))
    );

    static::assertEquals(
      new ResolvedToken('stdClass', 'text/plain'),
      ValueConverter::toResolvedToken(new \stdClass())
    );
  }

}
