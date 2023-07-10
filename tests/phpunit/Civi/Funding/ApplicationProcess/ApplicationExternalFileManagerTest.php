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

namespace Civi\Funding\ApplicationProcess;

use Civi\Funding\EntityFactory\ExternalFileFactory;
use Civi\Funding\FundingExternalFileManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\ApplicationProcess\ApplicationExternalFileManager
 */
final class ApplicationExternalFileManagerTest extends TestCase {

  private ApplicationExternalFileManager $applicationExternalFileManager;

  /**
   * @var \Civi\Funding\FundingExternalFileManagerInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $externalFileManagerMock;

  protected function setUp(): void {
    parent::setUp();
    $this->externalFileManagerMock = $this->createMock(FundingExternalFileManagerInterface::class);
    $this->applicationExternalFileManager = new ApplicationExternalFileManager($this->externalFileManagerMock);
  }

  public function testAddOrUpdateFile(): void {
    $externalFile = ExternalFileFactory::create();
    $this->externalFileManagerMock->method('addOrUpdateFile')
      ->with(
        'https://example.org/test.txt',
        'identifier',
        'civicrm_application_process',
        12,
        [
          'custom' => 'data',
          'entityName' => 'FundingApplicationProcess',
          'entityId' => 12,
        ]
      )->willReturn($externalFile);

    static::assertSame($externalFile, $this->applicationExternalFileManager->addOrUpdateFile(
      'https://example.org/test.txt',
      'identifier',
      12,
      ['custom' => 'data'],
    ));
  }

  public function testDeleteFile(): void {
    $externalFile = ExternalFileFactory::create();
    $this->externalFileManagerMock->expects(static::once())->method('deleteFile')
      ->with($externalFile);

    $this->applicationExternalFileManager->deleteFile($externalFile);
  }

  public function testDeleteFiles(): void {
    $this->externalFileManagerMock->expects(static::once())->method('deleteFiles')
      ->with('civicrm_application_process', 12, ['excluded_identifier']);

    $this->applicationExternalFileManager->deleteFiles(12, ['excluded_identifier']);
  }

  public function testGetFile(): void {
    $externalFile = ExternalFileFactory::create();
    $this->externalFileManagerMock->method('getFile')
      ->with('identifier', 'civicrm_application_process', 12)
      ->willReturn($externalFile);

    static::assertSame(
      $externalFile,
      $this->applicationExternalFileManager->getFile('identifier', 12)
    );
  }

  public function testGetFiles(): void {
    $externalFile = ExternalFileFactory::create();
    $this->externalFileManagerMock->method('getFiles')
      ->with('civicrm_application_process', 12)
      ->willReturn([$externalFile]);

    static::assertSame([$externalFile], $this->applicationExternalFileManager->getFiles(12));
  }

}
