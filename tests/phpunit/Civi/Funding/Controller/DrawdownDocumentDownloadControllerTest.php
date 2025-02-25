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

use Civi\Funding\EntityFactory\DrawdownFactory;
use Civi\Funding\FileTypeNames;
use Civi\Funding\FundingAttachmentManagerInterface;
use Civi\Funding\PayoutProcess\DrawdownManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @covers \Civi\Funding\Controller\DrawdownDocumentDownloadController
 */
final class DrawdownDocumentDownloadControllerTest extends TestCase {

  /**
   * @var \Civi\Funding\FundingAttachmentManagerInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $attachmentManagerMock;

  private DrawdownDocumentDownloadController $controller;

  /**
   * @var \Civi\Funding\PayoutProcess\DrawdownManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $drawdownManagerMock;

  protected function setUp(): void {
    parent::setUp();
    $this->attachmentManagerMock = $this->createMock(FundingAttachmentManagerInterface::class);
    $this->drawdownManagerMock = $this->createMock(DrawdownManager::class);
    $this->controller = new DrawdownDocumentDownloadController(
      $this->attachmentManagerMock,
      $this->drawdownManagerMock,
    );
  }

  public function testHandleDrawdownIdInvalid(): void {
    $request = new Request(['drawdownId' => 'abc']);

    $this->expectException(BadRequestHttpException::class);
    $this->expectExceptionMessage('Invalid drawdown ID');
    $this->controller->handle($request);
  }

  public function testHandleDrawdownIdUnauthorized(): void {
    $request = new Request(['drawdownId' => '12']);
    $this->drawdownManagerMock->method('get')
      ->with(12)
      ->willReturn(NULL);

    $this->expectException(AccessDeniedHttpException::class);
    $this->controller->handle($request);
  }

  public function testHandleDrawdownIdNotFoundPaymentInstruction(): void {
    $request = new Request(['drawdownId' => '1']);
    $this->drawdownManagerMock->expects(static::once())->method('get')
      ->with(1)
      ->willReturn(DrawdownFactory::create(['id' => 1]));
    $this->attachmentManagerMock->expects(static::once())->method('getLastByFileType')
      ->with('civicrm_funding_drawdown', 1, FileTypeNames::PAYMENT_INSTRUCTION)
      ->willReturn(NULL);

    $this->expectException(NotFoundHttpException::class);
    $this->expectExceptionMessage('Drawdown document (ID: 1) does not exist');
    $this->controller->handle($request);
  }

  public function testHandleDrawdownIdNotFoundPaybackClaim(): void {
    $request = new Request(['drawdownId' => '2']);
    $this->drawdownManagerMock->expects(static::once())->method('get')
      ->with(2)
      ->willReturn(DrawdownFactory::create(['id' => 2, 'amount' => -0.1]));
    $this->attachmentManagerMock->expects(static::once())->method('getLastByFileType')
      ->with('civicrm_funding_drawdown', 2, FileTypeNames::PAYBACK_CLAIM)
      ->willReturn(NULL);

    $this->expectException(NotFoundHttpException::class);
    $this->expectExceptionMessage('Drawdown document (ID: 2) does not exist');
    $this->controller->handle($request);
  }

  public function testHandleDrawdownIdsInvalid(): void {
    $request = new Request(['drawdownIds' => '1,2a']);
    $this->drawdownManagerMock->method('get')
      ->with(12)
      ->willReturn(NULL);

    $this->expectException(BadRequestHttpException::class);
    $this->expectExceptionMessage('Invalid drawdown IDs');
    $this->controller->handle($request);
  }

  public function testHandleDrawdownIdsUnauthorized(): void {
    $request = new Request(['drawdownIds' => '1,2']);
    $this->drawdownManagerMock->expects(static::once())->method('get')
      ->with(1)
      ->willReturn(NULL);

    $this->expectException(AccessDeniedHttpException::class);
    $this->controller->handle($request);
  }

  public function testHandleDrawdownIdsNotFoundPaymentInstruction(): void {
    $request = new Request(['drawdownIds' => '1,2']);
    $this->drawdownManagerMock->expects(static::once())->method('get')
      ->with(1)
      ->willReturn(DrawdownFactory::create(['id' => 1]));
    $this->attachmentManagerMock->expects(static::once())->method('getLastByFileType')
      ->with('civicrm_funding_drawdown', 1, FileTypeNames::PAYMENT_INSTRUCTION)
      ->willReturn(NULL);

    $this->expectException(NotFoundHttpException::class);
    $this->expectExceptionMessage('Drawdown document (ID: 1) does not exist');
    $this->controller->handle($request);
  }

  public function testHandleDrawdownIdsNotFoundPaybackClaim(): void {
    $request = new Request(['drawdownIds' => '2,3']);
    $this->drawdownManagerMock->expects(static::once())->method('get')
      ->with(2)
      ->willReturn(DrawdownFactory::create(['id' => 2, 'amount' => -0.1]));
    $this->attachmentManagerMock->expects(static::once())->method('getLastByFileType')
      ->with('civicrm_funding_drawdown', 2, FileTypeNames::PAYBACK_CLAIM)
      ->willReturn(NULL);

    $this->expectException(NotFoundHttpException::class);
    $this->expectExceptionMessage('Drawdown document (ID: 2) does not exist');
    $this->controller->handle($request);
  }

}
