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

use Civi\Funding\FundingAttachmentManagerInterface;
use Civi\Funding\PayoutProcess\DrawdownManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @covers \Civi\Funding\Controller\PaymentInstructionDownloadController
 */
final class PaymentInstructionDownloadControllerTest extends TestCase {

  /**
   * @var \Civi\Funding\FundingAttachmentManagerInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $attachmentManagerMock;

  private PaymentInstructionDownloadController $controller;

  /**
   * @var \Civi\Funding\PayoutProcess\DrawdownManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $drawdownManagerMock;

  protected function setUp(): void {
    parent::setUp();
    $this->attachmentManagerMock = $this->createMock(FundingAttachmentManagerInterface::class);
    $this->drawdownManagerMock = $this->createMock(DrawdownManager::class);
    $this->controller = new PaymentInstructionDownloadController(
      $this->attachmentManagerMock,
      $this->drawdownManagerMock,
    );
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
