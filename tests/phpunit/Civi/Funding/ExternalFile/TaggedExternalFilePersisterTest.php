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
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Systopia\JsonSchema\Tags\TaggedDataContainer;

/**
 * @covers \Civi\Funding\ExternalFile\TaggedExternalFilePersister
 */
final class TaggedExternalFilePersisterTest extends TestCase {

  /**
   * @var \Civi\Funding\ExternalFile\TaggedExternalFileManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $externalFileManagerMock;

  private TaggedExternalFilePersister $persister;

  protected function setUp(): void {
    parent::setUp();
    $this->externalFileManagerMock = $this->createMock(TaggedExternalFileManager::class);
    $this->persister = new TaggedExternalFilePersister($this->externalFileManagerMock);
  }

  public function testHandleFiles(): void {
    $data = ['file' => 'https://exaample.org/test.txt'];
    $taggedData = new TaggedDataContainer();
    $taggedData->add('externalFile', '/file', 'https://exaample.org/test.txt', NULL);

    $externalFile = ExternalFileFactory::create([
      'uri' => 'https://exaample.org/civi.txt',
      'custom_data' => ['dataPointer' => '/file'],
    ]);
    $this->externalFileManagerMock->expects(static::once())->method('updateAllFiles')
      ->with(['/file' => 'https://exaample.org/test.txt'])
      ->willReturn(['https://exaample.org/test.txt' => $externalFile]);

    static::assertSame(
      ['https://exaample.org/test.txt' => 'https://exaample.org/civi.txt'],
      $this->persister->handleFiles($taggedData, $data, 'TestEntity', 123)
    );
    static::assertSame(['file' => 'https://exaample.org/civi.txt'], $data);
  }

}
