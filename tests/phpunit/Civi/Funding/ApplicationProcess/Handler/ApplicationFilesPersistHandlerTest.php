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

namespace Civi\Funding\ApplicationProcess\Handler;

use Civi\Funding\ApplicationProcess\ApplicationExternalFileManagerInterface;
use Civi\Funding\ApplicationProcess\ApplicationFormFilesFactoryInterface;
use Civi\Funding\ApplicationProcess\Command\ApplicationFilesPersistCommand;
use Civi\Funding\EntityFactory\ApplicationProcessBundleFactory;
use Civi\Funding\EntityFactory\ApplicationProcessFactory;
use Civi\Funding\EntityFactory\ExternalFileFactory;
use Civi\Funding\Form\FundingFormFile;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\ApplicationProcess\Handler\ApplicationFilesPersistHandler
 * @covers \Civi\Funding\ApplicationProcess\Command\ApplicationFilesPersistCommand
 */
final class ApplicationFilesPersistHandlerTest extends TestCase {

  /**
   * @var \Civi\Funding\ApplicationProcess\ApplicationExternalFileManagerInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $externalFileManagerMock;

  /**
   * @var \Civi\Funding\ApplicationProcess\ApplicationFormFilesFactoryInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $formFilesFactoryMock;

  private ApplicationFilesPersistHandler $handler;

  protected function setUp(): void {
    parent::setUp();
    $this->externalFileManagerMock = $this->createMock(ApplicationExternalFileManagerInterface::class);
    $this->formFilesFactoryMock = $this->createMock(ApplicationFormFilesFactoryInterface::class);
    $this->handler = new ApplicationFilesPersistHandler(
      $this->externalFileManagerMock,
      $this->formFilesFactoryMock,
    );
  }

  public function testHandle(): void {
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle([
      'request_data' => ['foo' => 'bar'],
    ]);
    $previousApplicationProcess = ApplicationProcessFactory::createApplicationProcess();
    $command = new ApplicationFilesPersistCommand($applicationProcessBundle, $previousApplicationProcess);

    $this->formFilesFactoryMock->method('createFormFiles')
      ->with(['foo' => 'bar'])
      ->willReturn([
        FundingFormFile::new('https://example.org/test.txt', 'identifier', ['custom' => 'data']),
      ]);
    $externalFile = ExternalFileFactory::create(['identifier' => 'identifier']);
    $this->externalFileManagerMock->method('addOrUpdateFile')
      ->with(
        'https://example.org/test.txt',
        'identifier',
        $applicationProcessBundle->getApplicationProcess()->getId(),
        ['custom' => 'data'],
      )->willReturn($externalFile);
    $this->externalFileManagerMock->expects(static::once())->method('deleteFiles')
      ->with($applicationProcessBundle->getApplicationProcess()->getId(), ['identifier']);

    static::assertSame([
      'https://example.org/test.txt' => $externalFile,
    ], $this->handler->handle($command));
  }

}
