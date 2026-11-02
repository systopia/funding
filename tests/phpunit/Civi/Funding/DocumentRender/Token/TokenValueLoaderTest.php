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

namespace Civi\Funding\DocumentRender\Token;

use Civi\Api4\Generic\Result;
use Civi\Funding\EntityFactory\ApplicationProcessFactory;
use Civi\Funding\EntityFactory\FundingProgramFactory;
use Civi\RemoteTools\Api4\Api4Interface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * @covers \Civi\Funding\DocumentRender\Token\TokenValueLoader
 */
final class TokenValueLoaderTest extends TestCase {

  private Api4Interface&MockObject $api4Mock;

  private TokenValueLoader $tokenValueLoader;

  protected function setUp(): void {
    parent::setUp();
    $this->api4Mock = $this->createMock(Api4Interface::class);
    $this->tokenValueLoader = new TokenValueLoader(
      $this->api4Mock,
      PropertyAccess::createPropertyAccessor(),
    );
  }

  public function testGetValue(): void {
    $fundingProgram = FundingProgramFactory::createFundingProgram([
      'title' => 'test',
      '_extra' => ['foo'],
    ]);

    static::assertSame(
      'test',
      $this->tokenValueLoader->getValue('EntityName', $fundingProgram, 'title')
    );

    static::assertSame(
      ['foo'],
      $this->tokenValueLoader->getValue('EntityName', $fundingProgram, '_extra')
    );

    $this->api4Mock->method('execute')
      ->with('EntityName', 'get', [
        'select' => ['unknown'],
        'where' => [['id', '=', $fundingProgram->getId()]],
        'checkPermissions' => FALSE,
      ])->willReturn(new Result([['unknown' => 'value']]), new Result());

    static::assertSame(
      'value',
      $this->tokenValueLoader->getValue('EntityName', $fundingProgram, 'unknown')
    );

    static::expectException(\RuntimeException::class);
    static::expectExceptionMessage('Unknown token "unknown" for "EntityName"');
    $this->tokenValueLoader->getValue('EntityName', $fundingProgram, 'unknown');
  }

  public function testResolveTokenWithPath(): void {
    $applicationProcess = ApplicationProcessFactory::createApplicationProcess([
      'request_data' => ['foo' => ['bar' => 'baz']],
    ]);

    static::assertEquals(
      'baz',
      $this->tokenValueLoader->getValue('EntityName', $applicationProcess, 'request_data::foo.bar')
    );

    static::assertEquals(
      '',
      $this->tokenValueLoader->getValue('EntityName', $applicationProcess, 'request_data::foo.baz')
    );

    // Arrays are json encoded.
    static::assertEquals(
      '{"bar":"baz"}',
      $this->tokenValueLoader->getValue('EntityName', $applicationProcess, 'request_data::foo')
    );

    // Complete data is json encoded, if path is empty.
    static::assertEquals(
      '{"foo":{"bar":"baz"}}',
      $this->tokenValueLoader->getValue('EntityName', $applicationProcess, 'request_data::')
    );

    // Lists containing of scalars and null are kept as they are.
    $applicationProcess = ApplicationProcessFactory::createApplicationProcess([
      'request_data' => ['foo' => ['a', 1, 1.2, TRUE, FALSE, NULL]],
    ]);
    static::assertEquals(
      ['a', 1, 1.2, TRUE, FALSE, NULL],
      $this->tokenValueLoader->getValue('EntityName', $applicationProcess, 'request_data::foo')
    );
  }

}
