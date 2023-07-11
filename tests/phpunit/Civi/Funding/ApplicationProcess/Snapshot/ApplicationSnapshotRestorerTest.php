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

namespace Civi\Funding\ApplicationProcess\Snapshot;

use Civi\Funding\ApplicationProcess\ApplicationExternalFileManagerInterface;
use Civi\Funding\ApplicationProcess\ApplicationProcessManager;
use Civi\Funding\ApplicationProcess\ApplicationSnapshotManager;
use Civi\Funding\EntityFactory\ApplicationProcessBundleFactory;
use Civi\Funding\EntityFactory\ApplicationSnapshotFactory;
use Civi\Funding\EntityFactory\ExternalFileFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\ApplicationProcess\Snapshot\ApplicationSnapshotRestorer
 */
final class ApplicationSnapshotRestorerTest extends TestCase {

  /**
   * @var \Civi\Funding\ApplicationProcess\ApplicationProcessManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $applicationProcessManagerMock;

  /**
   * @var \Civi\Funding\ApplicationProcess\ApplicationSnapshotManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $applicationSnapshotManagerMock;

  private ApplicationSnapshotRestorer $applicationSnapshotRestorer;

  /**
   * @var \Civi\Funding\ApplicationProcess\ApplicationExternalFileManagerInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $externalFileManagerMock;

  protected function setUp(): void {
    parent::setUp();
    $this->applicationProcessManagerMock = $this->createMock(ApplicationProcessManager::class);
    $this->applicationSnapshotManagerMock = $this->createMock(ApplicationSnapshotManager::class);
    $this->externalFileManagerMock = $this->createMock(ApplicationExternalFileManagerInterface::class);
    $this->applicationSnapshotRestorer = new ApplicationSnapshotRestorer(
      $this->applicationProcessManagerMock,
      $this->applicationSnapshotManagerMock,
      $this->externalFileManagerMock,
    );
  }

  public function testRestoreLastSnapshot(): void {
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle();
    $applicationProcess = $applicationProcessBundle->getApplicationProcess();
    $applicationSnapshot = ApplicationSnapshotFactory::createApplicationSnapshot();

    $this->applicationSnapshotManagerMock->method('getLastByApplicationProcessId')
      ->with($applicationProcess->getId())
      ->willReturn($applicationSnapshot);

    $this->applicationProcessManagerMock->expects(static::once())->method('update')
      ->with(11, $applicationProcessBundle);

    $externalFile = ExternalFileFactory::create(['identifier' => 'testIdentifier']);
    $this->externalFileManagerMock->method('getFilesForSnapshot')
      ->with($applicationSnapshot->getId())
      ->willReturn([$externalFile]);
    $this->externalFileManagerMock->expects(static::once())->method('restoreSnapshot')
      ->with($externalFile);
    $this->externalFileManagerMock->expects(static::once())->method('deleteFiles')
      ->with($applicationProcess->getId(), ['testIdentifier']);

    $this->applicationSnapshotRestorer->restoreLastSnapshot(11, $applicationProcessBundle);

    static::assertSame($applicationSnapshot->getStatus(), $applicationProcess->getStatus());
    static::assertSame($applicationSnapshot->getTitle(), $applicationProcess->getTitle());
    static::assertSame($applicationSnapshot->getShortDescription(), $applicationProcess->getShortDescription());
    static::assertEquals($applicationSnapshot->getStartDate(), $applicationProcess->getStartDate());
    static::assertEquals($applicationSnapshot->getEndDate(), $applicationProcess->getEndDate());
    static::assertSame($applicationSnapshot->getRequestData(), $applicationProcess->getRequestData());
    static::assertSame($applicationSnapshot->getAmountRequested(), $applicationProcess->getAmountRequested());
    static::assertSame($applicationSnapshot->getIsReviewContent(), $applicationProcess->getIsReviewContent());
    static::assertSame($applicationSnapshot->getIsReviewCalculative(), $applicationProcess->getIsReviewCalculative());
    static::assertSame($applicationSnapshot->getIsEligible(), $applicationProcess->getIsEligible());
    static::assertSame($applicationSnapshot, $applicationProcess->getRestoredSnapshot());
  }

}
