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

use Civi\Api4\Generic\Result;
use Civi\Funding\EntityFactory\FundingProgramFactory;
use Civi\RemoteTools\Api4\Api4Interface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * @covers \Civi\Funding\DocumentRender\Token\TokenResolver
 */
final class TokenResolverTest extends TestCase {

  /**
   * @var \Civi\RemoteTools\Api4\Api4Interface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $api4Mock;

  /**
   * @var \Civi\Funding\DocumentRender\Token\TokenResolver
   */
  private TokenResolver $tokenResolver;

  protected function setUp(): void {
    parent::setUp();
    $this->api4Mock = $this->createMock(Api4Interface::class);
    $this->tokenResolver = new TokenResolver(
      $this->api4Mock,
      PropertyAccess::createPropertyAccessor(),
    );
  }

  public function testResolveToken(): void {
    $fundingProgram = FundingProgramFactory::createFundingProgram([
      'title' => 'test',
      '_extra' => ['foo'],
    ]);

    static::assertEquals(
      new ResolvedToken('test', 'text/plain'),
      $this->tokenResolver->resolveToken('EntityName', $fundingProgram, 'title')
    );

    static::assertEquals(
      new ResolvedToken('<p>    - foo</p>', 'text/html'),
      $this->tokenResolver->resolveToken('EntityName', $fundingProgram, '_extra')
    );

    $this->api4Mock->method('execute')
      ->with('EntityName', 'get', [
        'select' => ['unknown'],
        'where' => [['id', '=', $fundingProgram->getId()]],
        'checkPermissions' => FALSE,
      ])->willReturn(new Result([['unknown' => 'value']]), new Result());

    static::assertEquals(
      new ResolvedToken('value', 'text/plain'),
      $this->tokenResolver->resolveToken('EntityName', $fundingProgram, 'unknown')
    );

    static::expectException(\RuntimeException::class);
    static::expectExceptionMessage('Unknown token "unknown" for "EntityName"');
    $this->tokenResolver->resolveToken('EntityName', $fundingProgram, 'unknown');
  }

}
