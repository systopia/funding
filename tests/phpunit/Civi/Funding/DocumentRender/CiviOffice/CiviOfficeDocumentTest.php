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

use Civi\Funding\AbstractFundingHeadlessTestCase;
use Civi\Funding\Util\TestFileUtil;

/**
 * @covers \Civi\Funding\DocumentRender\CiviOffice\CiviOfficeDocument
 *
 * Headless test required for classes of de.systopia.civioffice being available.
 * @group headless
 */
final class CiviOfficeDocumentTest extends AbstractFundingHeadlessTestCase {

  protected function setUp(): void {
    if (!class_exists(\CRM_Civioffice_Document::class)) {
      static::markTestSkipped('Class CRM_Civioffice_Document is not available');
    }
    parent::setUp();
  }

  public function test(): void {
    $tmpFile = TestFileUtil::createTempFile();
    file_put_contents($tmpFile, 'test');
    $document = new CiviOfficeDocument(new CiviOfficeDocumentStore(), 'file://' . $tmpFile);
    static::assertSame($tmpFile, $document->getPath());
    static::assertSame('test', $document->getContent());
    static::assertTrue($document->isEditable());
    $document->updateFileContent('changed');
    static::assertSame('changed', file_get_contents($tmpFile));
    chmod($tmpFile, 0);
    static::assertFalse($document->isEditable());
    unlink($tmpFile);
  }

}
