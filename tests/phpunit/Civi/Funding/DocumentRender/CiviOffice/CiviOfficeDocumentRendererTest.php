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

namespace Civi\Funding\DocumentRender\CiviOffice;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\DocumentRender\CiviOffice\CiviOfficeDocumentRenderer
 */
final class CiviOfficeDocumentRendererTest extends TestCase {

  private CiviOfficeDocumentRenderer $documentRenderer;

  /**
   * @var \Civi\Funding\DocumentRender\CiviOffice\CiviOfficeRenderer&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $rendererMock;

  protected function setUp(): void {
    parent::setUp();
    $this->rendererMock = $this->createMock(CiviOfficeRenderer::class);
    $this->documentRenderer = new CiviOfficeDocumentRenderer($this->rendererMock);
  }

  public function testGetMimeType(): void {
    $this->rendererMock->method('getMimeType')
      ->willReturn('application/test');
    static::assertSame('application/test', $this->documentRenderer->getMimeType());
  }

  public function testRender(): void {
    $this->rendererMock->method('render')
      ->with('file://template.abc', 'TestEntity', 2, ['foo' => 'bar'])
      ->willReturn('/result/file.test');

    static::assertSame(
      '/result/file.test',
      $this->documentRenderer->render('template.abc', 'TestEntity', 2, ['foo' => 'bar'])
    );
  }

}
