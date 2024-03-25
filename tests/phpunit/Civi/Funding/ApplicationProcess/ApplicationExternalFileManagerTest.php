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

use Civi\Api4\FundingApplicationProcess;
use Civi\Api4\FundingApplicationSnapshot;
use Civi\Funding\EntityFactory\ExternalFileFactory;
use Civi\Funding\ExternalFile\FundingExternalFileManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\PhpUnit\ClockMock;

/**
 * @covers \Civi\Funding\ApplicationProcess\ApplicationExternalFileManager
 */
final class ApplicationExternalFileManagerTest extends TestCase {

  private ApplicationExternalFileManager $applicationExternalFileManager;

  /**
   * @var \Civi\Funding\ExternalFile\FundingExternalFileManagerInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $externalFileManagerMock;

  public static function setUpBeforeClass(): void {
    parent::setUpBeforeClass();
    ;
    ClockMock::register(__CLASS__);
    ClockMock::withClockMock(123456789);
  }

  protected function setUp(): void {
    parent::setUp();
    $this->externalFileManagerMock = $this->createMock(FundingExternalFileManagerInterface::class);
    $this->applicationExternalFileManager = new ApplicationExternalFileManager($this->externalFileManagerMock);
  }

  public function testAddOrUpdateFileNew(): void {
    $externalFile = ExternalFileFactory::create();
    $this->externalFileManagerMock->method('getFile')
      ->with('FundingApplicationProcess.12:identifier', FundingApplicationProcess::getEntityName(), 12)
      ->willReturn(NULL);

    $this->externalFileManagerMock->method('addOrUpdateFile')
      ->with(
        'https://example.org/test.txt',
        'FundingApplicationProcess.12:identifier',
        FundingApplicationProcess::getEntityName(),
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
      [
        'custom' => 'data',
        'entityName' => 'FundingApplicationProcess',
        'entityId' => 12,
      ],
    ));
  }

  public function testAddOrUpdateFileUnchanged(): void {
    $externalFile = ExternalFileFactory::create();
    $this->externalFileManagerMock->method('getFile')
      ->with('FundingApplicationProcess.12:identifier', FundingApplicationProcess::getEntityName(), 12)
      ->willReturn($externalFile);
    $this->externalFileManagerMock->method('isFileChanged')
      ->with($externalFile, 'https://example.org/test.txt')
      ->willReturn(FALSE);
    $this->externalFileManagerMock->expects(static::never())->method('updateIdentifier');

    $externalFileNew = ExternalFileFactory::create();
    $this->externalFileManagerMock->method('addOrUpdateFile')
      ->with(
        'https://example.org/test.txt',
        'FundingApplicationProcess.12:identifier',
        FundingApplicationProcess::getEntityName(),
        12,
        [
          'custom' => 'data',
          'entityName' => 'FundingApplicationProcess',
          'entityId' => 12,
        ]
      )->willReturn($externalFileNew);

    static::assertSame($externalFileNew, $this->applicationExternalFileManager->addOrUpdateFile(
      'https://example.org/test.txt',
      'identifier',
      12,
      [
        'custom' => 'data',
        'entityName' => 'FundingApplicationProcess',
        'entityId' => 12,
      ],
    ));
  }

  public function testAddOrUpdateFileChangedWithoutSnapshot(): void {
    $externalFile = ExternalFileFactory::create();
    $this->externalFileManagerMock->method('getFile')
      ->with('FundingApplicationProcess.12:identifier', FundingApplicationProcess::getEntityName(), 12)
      ->willReturn($externalFile);
    $this->externalFileManagerMock->method('isFileChanged')
      ->with($externalFile, 'https://example.org/test.txt')
      ->willReturn(TRUE);
    $this->externalFileManagerMock->method('isAttachedToEntityType')
      ->with($externalFile, FundingApplicationSnapshot::getEntityName())
      ->willReturn(FALSE);

    $this->externalFileManagerMock->expects(static::once())->method('deleteFile')
      ->with($externalFile);

    $externalFileUpdated = ExternalFileFactory::create();
    $this->externalFileManagerMock->method('addFile')
      ->with(
        'https://example.org/test.txt',
        'FundingApplicationProcess.12:identifier',
        FundingApplicationProcess::getEntityName(),
        12,
        [
          'custom' => 'data',
          'entityName' => 'FundingApplicationProcess',
          'entityId' => 12,
        ]
      )->willReturn($externalFileUpdated);

    static::assertSame($externalFileUpdated, $this->applicationExternalFileManager->addOrUpdateFile(
      'https://example.org/test.txt',
      'identifier',
      12,
      [
        'custom' => 'data',
        'entityName' => 'FundingApplicationProcess',
        'entityId' => 12,
      ],
    ));
  }

  public function testAddOrUpdateFileChangedWithSnapshot(): void {
    $externalFileExisting = ExternalFileFactory::create(['identifier' => 'FundingApplicationProcess.12:identifier']);
    $this->externalFileManagerMock->method('getFile')
      ->with('FundingApplicationProcess.12:identifier', FundingApplicationProcess::getEntityName(), 12)
      ->willReturn($externalFileExisting);
    $this->externalFileManagerMock->method('isFileChanged')
      ->with($externalFileExisting, 'https://example.org/test.txt')
      ->willReturn(TRUE);
    $this->externalFileManagerMock->method('isAttachedToEntityType')
      ->with($externalFileExisting, FundingApplicationSnapshot::getEntityName())
      ->willReturn(TRUE);

    $this->externalFileManagerMock->expects(static::once())->method('updateIdentifier')
      ->with($externalFileExisting, 'snapshot@123456789:FundingApplicationProcess.12:identifier');
    $this->externalFileManagerMock->expects(static::once())->method('detachFile')
      ->with($externalFileExisting, FundingApplicationProcess::getEntityName(), 12);

    $externalFileNew = ExternalFileFactory::create();
    $this->externalFileManagerMock->method('addFile')
      ->with(
        'https://example.org/test.txt',
        'FundingApplicationProcess.12:identifier',
        FundingApplicationProcess::getEntityName(),
        12,
        [
          'custom' => 'data',
          'entityName' => 'FundingApplicationProcess',
          'entityId' => 12,
        ]
      )->willReturn($externalFileNew);

    static::assertSame($externalFileNew, $this->applicationExternalFileManager->addOrUpdateFile(
      'https://example.org/test.txt',
      'identifier',
      12,
      [
        'custom' => 'data',
        'entityName' => 'FundingApplicationProcess',
        'entityId' => 12,
      ],
    ));
  }

  public function testDeleteFiles(): void {
    $externalFile = ExternalFileFactory::create(['identifier' => 'FundingApplicationProcess.12:identifier']);
    $this->externalFileManagerMock->method('getFiles')
      ->with(FundingApplicationProcess::getEntityName(), 12)
      ->willReturn([$externalFile]);

    $this->externalFileManagerMock->expects(static::once())->method('deleteFile')
      ->with($externalFile);

    $this->applicationExternalFileManager->deleteFiles(12, [
      'excluded_identifier',
    ]);
  }

  public function testDeleteFilesExcluded(): void {
    $externalFile1 = ExternalFileFactory::create(['identifier' => 'FundingApplicationProcess.12:excluded_identifier1']);
    $externalFile2 = ExternalFileFactory::create(['identifier' => 'FundingApplicationProcess.12:excluded_identifier2']);
    $this->externalFileManagerMock->method('getFiles')
      ->with(FundingApplicationProcess::getEntityName(), 12)
      ->willReturn([$externalFile1, $externalFile2]);

    $this->externalFileManagerMock->expects(static::never())->method('deleteFile');

    $this->applicationExternalFileManager->deleteFiles(12, [
      'excluded_identifier1',
      'FundingApplicationProcess.12:excluded_identifier2',
    ]);
  }

  public function testDeleteFilesWithSnapshot(): void {
    $externalFile = ExternalFileFactory::create(['identifier' => 'FundingApplicationProcess.12:identifier']);
    $this->externalFileManagerMock->method('getFiles')
      ->with(FundingApplicationProcess::getEntityName(), 12)
      ->willReturn([$externalFile]);

    $this->externalFileManagerMock->method('isAttachedToEntityType')
      ->with($externalFile, FundingApplicationSnapshot::getEntityName())
      ->willReturn(TRUE);

    $this->externalFileManagerMock->expects(static::once())->method('updateIdentifier')
      ->with($externalFile, 'snapshot@123456789:FundingApplicationProcess.12:identifier');
    $this->externalFileManagerMock->expects(static::once())->method('detachFile')
      ->with($externalFile, FundingApplicationProcess::getEntityName(), 12);

    $this->externalFileManagerMock->expects(static::never())->method('deleteFile');

    $this->applicationExternalFileManager->deleteFiles(12, [
      'excluded_identifier',
    ]);
  }

  public function testGetFile(): void {
    $externalFile = ExternalFileFactory::create();
    $this->externalFileManagerMock->method('getFile')
      ->with('FundingApplicationProcess.12:identifier', FundingApplicationProcess::getEntityName(), 12)
      ->willReturn($externalFile);

    static::assertSame(
      $externalFile,
      $this->applicationExternalFileManager->getFile('identifier', 12)
    );
  }

  public function testGetFiles(): void {
    $externalFile = ExternalFileFactory::create(['identifier' => 'FundingApplicationProcess.12:identifier']);
    $this->externalFileManagerMock->method('getFiles')
      ->with(FundingApplicationProcess::getEntityName(), 12)
      ->willReturn([$externalFile]);

    static::assertSame(
      ['identifier' => $externalFile],
      $this->applicationExternalFileManager->getFiles(12),
    );
  }

}
