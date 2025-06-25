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

namespace Civi\Funding\Controller;

use Civi\Api4\FundingDrawdown;
use Civi\Api4\Generic\Result;
use Civi\Funding\EntityFactory\DrawdownFactory;
use Civi\Funding\EntityFactory\PayoutProcessFactory;
use Civi\Funding\PayoutProcess\DrawdownManager;
use Civi\Funding\PayoutProcess\PayoutProcessManager;
use Civi\Funding\Util\UrlGenerator;
use Civi\RemoteTools\Api4\Api4Interface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @covers \Civi\Funding\Controller\DrawdownAcceptController
 */
final class DrawdownAcceptControllerTest extends TestCase {

  /**
   * @var \Civi\RemoteTools\Api4\Api4Interface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $api4MockMock;

  private DrawdownAcceptController $controller;

  /**
   * @var \Civi\Funding\PayoutProcess\DrawdownManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $drawdownManagerMock;

  /**
   * @var \Civi\Funding\PayoutProcess\PayoutProcessManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $payoutProcessManagerMock;

  /**
   * @var \Civi\Funding\Util\UrlGenerator&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $urlGeneratorMock;

  protected function setUp(): void {
    parent::setUp();
    $this->api4MockMock = $this->createMock(Api4Interface::class);
    $this->drawdownManagerMock = $this->createMock(DrawdownManager::class);
    $this->payoutProcessManagerMock = $this->createMock(PayoutProcessManager::class);
    $this->urlGeneratorMock = $this->createMock(UrlGenerator::class);
    $this->controller = new DrawdownAcceptController(
      $this->api4MockMock,
      $this->drawdownManagerMock,
      $this->payoutProcessManagerMock,
      $this->urlGeneratorMock,
    );
  }

  /**
   * @dataProvider provideReferrer
   */
  public function testHandle(?string $referrer, ?string $expectedRedirect = NULL): void {
    $drawdown = DrawdownFactory::create();
    $this->drawdownManagerMock->method('get')
      ->with($drawdown->getId())
      ->willReturn($drawdown);
    $payoutProcess = PayoutProcessFactory::create();
    $this->payoutProcessManagerMock->method('get')
      ->with($payoutProcess->getId())
      ->willReturn($payoutProcess);

    $this->api4MockMock->expects(static::once())->method('execute')
      ->with(FundingDrawdown::getEntityName(), 'accept', [
        'id' => $drawdown->getId(),
      ])->willReturn(new Result([['status' => 'accepted'] + $drawdown->toArray()]));

    $this->urlGeneratorMock->method('generate')
      ->with('civicrm/a#/funding/case/' . $payoutProcess->getFundingCaseId())
      ->willReturn('http://test');

    $request = new Request(['drawdownId' => $drawdown->getId()]);
    if (NULL !== $referrer) {
      $request->headers->set('Referer', $referrer);
    }

    $response = $this->controller->handle($request);
    static::assertInstanceOf(RedirectResponse::class, $response);
    static::assertSame($expectedRedirect ?? 'http://test', $response->getTargetUrl());
  }

  /**
   * @phpstan-return iterable<array{string|null, 1?: string}>
   */
  public function provideReferrer(): iterable {
    yield [NULL];
    yield ['http://test/civicrm/funding/abc', 'http://test/civicrm/funding/abc'];
    yield ['http://test/civicrm/a'];
  }

  public function testHandleInvalidDrawdownId(): void {
    $request = new Request(['drawdownId' => 'abc']);
    $this->drawdownManagerMock->method('get')
      ->with(12)
      ->willReturn(NULL);

    $this->expectException(BadRequestHttpException::class);
    $this->expectExceptionMessage('Invalid drawdown ID');
    $this->controller->handle($request);
  }

  public function testHandleUnauthorized(): void {
    $request = new Request(['drawdownId' => '12']);
    $this->drawdownManagerMock->method('get')
      ->with(12)
      ->willReturn(NULL);

    $this->expectException(AccessDeniedHttpException::class);
    $this->controller->handle($request);
  }

}
