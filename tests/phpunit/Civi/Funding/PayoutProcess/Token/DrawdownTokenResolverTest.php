<?php
/*
 * Copyright (C) 2024 SYSTOPIA GmbH
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

namespace Civi\Funding\PayoutProcess\Token;

use Civi\Api4\Generic\Result;
use Civi\Funding\DocumentRender\Token\ResolvedToken;
use Civi\Funding\DocumentRender\Token\TokenResolverInterface;
use Civi\Funding\EntityFactory\DrawdownFactory;
use Civi\RemoteTools\Api4\Api4Interface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\PayoutProcess\Token\DrawdownTokenResolver
 */
final class DrawdownTokenResolverTest extends TestCase {

  private Api4Interface&MockObject $api4Mock;

  private DrawdownTokenResolver $drawdownTokenResolver;

  /**
   * @phpstan-ignore missingType.generics
   */
  private TokenResolverInterface&MockObject $tokenResolverMock;

  protected function setUp(): void {
    parent::setUp();
    $this->api4Mock = $this->createMock(Api4Interface::class);
    $this->tokenResolverMock = $this->createMock(TokenResolverInterface::class);

    $this->drawdownTokenResolver = new DrawdownTokenResolver(
      $this->api4Mock,
      $this->tokenResolverMock
    );
  }

  public function testResolveTokenReviewerContactDisplayName(): void {
    $drawdown = DrawdownFactory::create(['reviewer_contact_id' => NULL]);
    static::assertEquals(
      new ResolvedToken('', 'text/plain'),
      $this->drawdownTokenResolver->resolveToken('Drawdown', $drawdown, 'reviewer_contact_display_name')
    );

    $drawdown = DrawdownFactory::create(['reviewer_contact_id' => 12]);
    $this->api4Mock->method('execute')
      ->with('Contact', 'get', [
        'select' => ['id', 'display_name'],
        'where' => [['id', '=', 12]],
      ])->willReturn(new Result([['id' => 12, 'display_name' => 'Some Name']]));

    static::assertEquals(
      new ResolvedToken('Some Name', 'text/plain'),
      $this->drawdownTokenResolver->resolveToken('Drawdown', $drawdown, 'reviewer_contact_display_name')
    );
  }

  public function testResolveTokenRequesterContactDisplayName(): void {
    $drawdown = DrawdownFactory::create(['requester_contact_id' => 12]);
    $this->api4Mock->method('execute')
      ->with('Contact', 'get', [
        'select' => ['id', 'display_name'],
        'where' => [['id', '=', 12]],
      ])->willReturn(new Result([['id' => 12, 'display_name' => 'Some Name']]));

    static::assertEquals(
      new ResolvedToken('Some Name', 'text/plain'),
      $this->drawdownTokenResolver->resolveToken('Drawdown', $drawdown, 'requester_contact_display_name')
    );
  }

  public function testResolveTokenOther(): void {
    $drawdown = DrawdownFactory::create();
    $resolvedToken = new ResolvedToken('value', 'text/plain');
    $this->tokenResolverMock->method('resolveToken')
      ->with('Drawdown', $drawdown, 'some_token')
      ->willReturn($resolvedToken);

    static::assertSame(
      $resolvedToken,
      $this->drawdownTokenResolver->resolveToken('Drawdown', $drawdown, 'some_token'));
  }

}
