<?php
/*
 * Copyright (C) 2026 SYSTOPIA GmbH
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

namespace Civi\Funding\DocumentRender\CiviOffice;

use Civi\Api4\Generic\Result;
use Civi\RemoteTools\Api4\Api4Interface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\DocumentRender\CiviOffice\CiviOfficeRendererOptions
 */
class CiviOfficeRendererOptionsTest extends TestCase {

  public function testFetchOptions(): void {
    $api4 = $this->createMock(Api4Interface::class);

    $result = $this->createMock(Result::class);
    $result->method('getIterator')->willReturn(new \ArrayIterator([
      ['uri' => 'unoconv-1', 'name' => 'unoconv 1'],
      ['uri' => 'unoconv-2', 'name' => 'unoconv 2'],
    ]));

    $api4->expects(static::once())
      ->method('execute')
      ->with('CiviofficeRenderer', 'get', [
        'where' => [
          ['is_active', '=', TRUE],
        ],
        'checkPermissions' => TRUE,
      ])
      ->willReturn($result);

    $options = (new CiviOfficeRendererOptions($api4))->fetchOptions();

    static::assertEquals([
      'unoconv-1' => 'unoconv 1',
      'unoconv-2' => 'unoconv 2',
    ], $options);
  }

}
