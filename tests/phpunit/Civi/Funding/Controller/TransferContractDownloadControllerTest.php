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
use Civi\Funding\FundingCase\FundingCaseManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @covers \Civi\Funding\Controller\TransferContractDownloadController
 */
final class TransferContractDownloadControllerTest extends TestCase {

  /**
   * @var \Civi\Funding\FundingAttachmentManagerInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $attachmentManagerMock;

  private TransferContractDownloadController $controller;

  /**
   * @var \Civi\Funding\FundingCase\FundingCaseManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $fundingCaseManagerMock;

  protected function setUp(): void {
    parent::setUp();
    $this->attachmentManagerMock = $this->createMock(FundingAttachmentManagerInterface::class);
    $this->fundingCaseManagerMock = $this->createMock(FundingCaseManager::class);
    $this->controller = new TransferContractDownloadController(
      $this->attachmentManagerMock,
      $this->fundingCaseManagerMock,
    );
  }

  public function testHandleInvalidFundingCaseId(): void {
    $request = new Request(['fundingCaseId' => 'abc']);
    $this->fundingCaseManagerMock->method('hasAccess')
      ->with(12)
      ->willReturn(FALSE);

    $this->expectException(BadRequestHttpException::class);
    $this->expectExceptionMessage('Invalid funding case ID');
    $this->controller->handle($request);
  }

  public function testHandleUnauthorized(): void {
    $request = new Request(['fundingCaseId' => '12']);
    $this->fundingCaseManagerMock->method('hasAccess')
      ->with(12)
      ->willReturn(FALSE);

    $this->expectException(AccessDeniedHttpException::class);
    $this->controller->handle($request);
  }

}
