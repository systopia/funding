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

namespace Civi\Funding\ApplicationProcess;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\ApplicationProcess\ItemsIdentifierUtil
 */
final class ItemsIdentifierUtilTest extends TestCase {

  public function testAddIdentifiers(): void {
    $data = [
      'without_identifier' => ['foo1' => 'bar1'],
      'empty_identifier' => ['foo2' => 'bar2', 'ident' => ''],
      'with_identifier' => ['foo3' => 'bar3', 'ident' => 'x'],
    ];

    $result = ItemsIdentifierUtil::addIdentifiers($data, 'ident');
    static::assertArrayHasKey('ident', $result['without_identifier']);
    static::assertIsString($result['without_identifier']['ident']);
    static::assertNotEmpty($result['without_identifier']['ident']);

    static::assertNotEmpty($result['empty_identifier']['ident']);

    static::assertSame('x', $result['with_identifier']['ident']);
  }

}
