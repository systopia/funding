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

namespace Civi\Funding\Controller;

use Civi\Funding\EntityFactory\AttachmentFactory;
use Civi\Funding\EntityFactory\DrawdownFactory;
use Civi\Funding\FileTypeNames;
use Civi\Funding\FundingAttachmentManagerInterface;
use Civi\Funding\PayoutProcess\DrawdownManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @covers \Civi\Funding\Controller\DrawdownSubmitConfirmationDownloadController
 */
final class DrawdownSubmitConfirmationDownloadControllerTest extends TestCase {

  private FundingAttachmentManagerInterface&MockObject $attachmentManagerMock;

  private DrawdownSubmitConfirmationDownloadController $controller;

  private MockObject&DrawdownManager $drawdownManagerMock;

  protected function setUp(): void {
    parent::setUp();
    $this->attachmentManagerMock = $this->createMock(FundingAttachmentManagerInterface::class);
    $this->drawdownManagerMock = $this->createMock(DrawdownManager::class);
    $this->controller = new DrawdownSubmitConfirmationDownloadController(
      $this->attachmentManagerMock,
      $this->drawdownManagerMock
    );
  }

  public function testInvalidDrawdownId(): void {
    $request = new Request(['drawdownId' => 'abc']);

    $this->expectException(BadRequestHttpException::class);
    $this->controller->handle($request);
  }

  public function testNoDrawdown(): void {
    $request = new Request(['drawdownId' => 123]);

    $drawdown = DrawdownFactory::create();
    $this->drawdownManagerMock->expects(static::once())->method('get')
      ->with(123)
      ->willReturn(NULL);

    $this->expectException(NotFoundHttpException::class);
    $this->controller->handle($request);
  }

  public function testNoSubmitConfirmation(): void {
    $request = new Request(['drawdownId' => 123]);

    $drawdown = DrawdownFactory::create();
    $this->drawdownManagerMock->expects(static::once())->method('get')
      ->with(123)
      ->willReturn($drawdown);

    $this->attachmentManagerMock->expects(static::once())->method('getLastByFileType')
      ->with('civicrm_funding_drawdown', 123, FileTypeNames::DRAWDOWN_SUBMIT_CONFIRMATION)
      ->willReturn(NULL);

    $this->expectException(NotFoundHttpException::class);
    $this->controller->handle($request);
  }

  public function test(): void {
    $request = new Request(['drawdownId' => 123]);

    $drawdown = DrawdownFactory::create();
    $this->drawdownManagerMock->expects(static::once())->method('get')
      ->with(123)
      ->willReturn($drawdown);

    $file = tmpfile();
    // @phpstan-ignore-next-line
    $path = stream_get_meta_data($file)['uri'];
    $attachment = AttachmentFactory::create([
      'entity_table' => 'civicrm_funding_drawdown',
      'entity_id' => 123,
      'path' => $path,
    ]);
    $this->attachmentManagerMock->expects(static::once())->method('getLastByFileType')
      ->with('civicrm_funding_drawdown', 123, FileTypeNames::DRAWDOWN_SUBMIT_CONFIRMATION)
      ->willReturn($attachment);

    $response = $this->controller->handle($request);
    static::assertInstanceOf(BinaryFileResponse::class, $response);

    static::assertSame($path, $response->getFile()->getPathname());
  }

}
