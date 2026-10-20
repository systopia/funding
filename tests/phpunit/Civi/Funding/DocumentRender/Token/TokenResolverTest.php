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

use Brick\Money\Money;
use Civi\Api4\Generic\Result;
use Civi\Funding\EntityFactory\FundingProgramFactory;
use Civi\Funding\Util\MoneyFactory;
use Civi\RemoteTools\Api4\Api4Interface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\DocumentRender\Token\TokenResolver
 */
final class TokenResolverTest extends TestCase {

  private Api4Interface&MockObject $api4Mock;

  private TokenResolver $tokenResolver;

  private TokenValueLoader&MockObject $tokenValueLoaderMock;

  protected function setUp(): void {
    parent::setUp();
    $this->api4Mock = $this->createMock(Api4Interface::class);
    $this->tokenValueLoaderMock = $this->createMock(TokenValueLoader::class);
    $this->tokenResolver = new TokenResolver(
      $this->api4Mock,
      new MoneyFactory(),
      $this->tokenValueLoaderMock,
    );
  }

  public function testResolveToken(): void {
    $fundingProgram = FundingProgramFactory::createFundingProgram();

    $this->tokenValueLoaderMock->method('getValue')
      ->with('EntityName', $fundingProgram, 'someField')
      ->willReturn('test', ['foo']);

    static::assertEquals(
      new ResolvedToken('test', 'text/plain'),
      $this->tokenResolver->resolveToken('EntityName', $fundingProgram, 'someField')
    );

    static::assertEquals(
      new ResolvedToken("    - foo<br />\n", 'text/html'),
      $this->tokenResolver->resolveToken('EntityName', $fundingProgram, 'someField')
    );
  }

  public function testResolveTokenWithFloat(): void {
    $fundingProgram = FundingProgramFactory::createFundingProgram(['currency' => 'EUR']);

    $api4Series = [
      [
        [
          'FundingProgram', 'getFields', [
            'select' => ['data_type'],
            'where' => [['name', '=', 'budget']],
            'checkPermissions' => FALSE,
          ],
        ],
        new Result([['data_type' => 'Money']]),
      ],
      [
        [
          'FundingProgram', 'getFields', [
            'select' => ['data_type'],
            'where' => [['name', '=', 'nonMoneyFloat']],
            'checkPermissions' => FALSE,
          ],
        ],
        new Result([['data_type' => 'Float']]),
      ],
    ];
    $this->api4Mock->method('execute')
      ->willReturnCallback(function (...$args) use (&$api4Series) {
        // @phpstan-ignore offsetAccess.nonArray
        [$expectedArgs, $return] = array_shift($api4Series);
        static::assertEquals($expectedArgs, $args);

        return $return;
      });

    $this->tokenValueLoaderMock->method('getValue')
      ->willReturnCallback(function ($entityName, $entity, $fieldName) {
        return 'budget' === $fieldName ? 12345.67 : 1.23;
      });

    $resolvedBudget = $this->tokenResolver->resolveToken('FundingProgram', $fundingProgram, 'budget');
    static::assertInstanceOf(Money::class, $resolvedBudget->value);
    static::assertSame(12345.67, $resolvedBudget->value->getAmount()->toFloat());
    static::assertSame('EUR', $resolvedBudget->value->getCurrency()->getCurrencyCode());

    static::assertEquals(
      new ResolvedToken('1.23', 'text/plain'),
      $this->tokenResolver->resolveToken('FundingProgram', $fundingProgram, 'nonMoneyFloat'),
    );
  }

}
