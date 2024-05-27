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

namespace Civi\Funding\ExternalFile;

use Civi\Funding\EntityFactory\ExternalFileFactory;
use Civi\RemoteTools\Api4\Query\Comparison;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\ExternalFile\TaggedExternalFileManager
 */
final class TaggedExternalFileManagerTest extends TestCase {

  /**
   * @var \Civi\Funding\ExternalFile\FundingExternalFileManagerInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $externalFileManagerMock;

  private TaggedExternalFileManager $taggedExternalFileManager;

  protected function setUp(): void {
    parent::setUp();
    $this->externalFileManagerMock = $this->createMock(FundingExternalFileManagerInterface::class);
    $this->taggedExternalFileManager = new TaggedExternalFileManager($this->externalFileManagerMock);
  }

  public function testGetFiles(): void {
    $externalFile = ExternalFileFactory::create(['uri' => 'https://example.org/test.txt']);
    $this->externalFileManagerMock->method('getFiles')
      ->with('TestEntity', 123, Comparison::new('identifier', 'LIKE', 'tagged:%'))
      ->willReturn([$externalFile]);

    static::assertSame(
      ['https://example.org/test.txt' => $externalFile],
      $this->taggedExternalFileManager->getFiles('TestEntity', 123)
    );
  }

  public function testUpdateAllFiles(): void {
    $uris = [
      '/uri1' => 'https://example.org/new.txt',
      '/uri2' => 'https://example.org/existing.txt',
    ];

    $existingFile = ExternalFileFactory::create([
      'uri' => 'https://example.org/existing.txt',
      'customData' => ['dataPointer' => '/oldPath'],
    ]);
    $toBeDeletedFile = ExternalFileFactory::create(['uri' => 'https://example.org/toBeDeleted.txt']);
    $this->externalFileManagerMock->method('getFiles')
      ->with('TestEntity', 123, Comparison::new('identifier', 'LIKE', 'tagged:%'))
      ->willReturn([$existingFile, $toBeDeletedFile]);

    $this->externalFileManagerMock->expects(static::once())->method('updateCustomData')
      ->with($existingFile, ['dataPointer' => '/uri2']);

    $newFile = ExternalFileFactory::create(['uri' => 'https://example.org/new.txt']);
    $this->externalFileManagerMock->expects(static::once())->method('addFile')
      ->with(
        'https://example.org/new.txt',
        static::stringStartsWith('tagged:'),
        'TestEntity',
        123,
        ['dataPointer' => '/uri1']
      )
      ->willReturn($newFile);

    $this->externalFileManagerMock->expects(static::once())->method('deleteFile')
      ->with($toBeDeletedFile);

    static::assertEquals(
      [
        $uris['/uri1'] => $newFile,
        $uris['/uri2'] => $existingFile,
      ],
      $this->taggedExternalFileManager->updateAllFiles($uris, 'TestEntity', 123)
    );
  }

}
