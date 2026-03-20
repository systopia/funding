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

use Civi\Core\CiviEventDispatcherInterface;
use Civi\Funding\ApplicationProcess\ApplicationCostItemManager;
use Civi\Funding\ApplicationProcess\ApplicationExternalFileManagerInterface;
use Civi\Funding\ApplicationProcess\ApplicationProcessManager;
use Civi\Funding\ApplicationProcess\ApplicationResourcesItemManager;
use Civi\Funding\ApplicationProcess\ApplicationSnapshotManager;
use Civi\Funding\EntityFactory\ApplicationCostItemFactory;
use Civi\Funding\EntityFactory\ApplicationProcessBundleFactory;
use Civi\Funding\EntityFactory\ApplicationResourcesItemFactory;
use Civi\Funding\EntityFactory\ApplicationSnapshotFactory;
use Civi\Funding\EntityFactory\ExternalFileFactory;
use Civi\Funding\Event\ApplicationProcess\ApplicationSnapshotRestoredEvent;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\ApplicationProcess\Snapshot\ApplicationSnapshotRestorer
 */
final class ApplicationSnapshotRestorerTest extends TestCase {

  private ApplicationProcessManager&MockObject $applicationProcessManagerMock;

  private ApplicationSnapshotManager&MockObject $applicationSnapshotManagerMock;

  private ApplicationSnapshotRestorer $applicationSnapshotRestorer;

  private ApplicationCostItemManager&MockObject $costItemManagerMock;

  private CiviEventDispatcherInterface&MockObject $eventDispatcherMock;

  private ApplicationExternalFileManagerInterface&MockObject $externalFileManagerMock;

  private ApplicationResourcesItemManager&MockObject $resourcesItemManagerMock;

  protected function setUp(): void {
    parent::setUp();
    $this->applicationProcessManagerMock = $this->createMock(ApplicationProcessManager::class);
    $this->applicationSnapshotManagerMock = $this->createMock(ApplicationSnapshotManager::class);
    $this->costItemManagerMock = $this->createMock(ApplicationCostItemManager::class);
    $this->eventDispatcherMock = $this->createMock(CiviEventDispatcherInterface::class);
    $this->externalFileManagerMock = $this->createMock(ApplicationExternalFileManagerInterface::class);
    $this->resourcesItemManagerMock = $this->createMock(ApplicationResourcesItemManager::class);
    $this->applicationSnapshotRestorer = new ApplicationSnapshotRestorer(
      $this->applicationProcessManagerMock,
      $this->applicationSnapshotManagerMock,
      $this->costItemManagerMock,
      $this->eventDispatcherMock,
      $this->externalFileManagerMock,
      $this->resourcesItemManagerMock
    );
  }

  public function testRestoreLastSnapshot(): void {
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle();
    $applicationProcess = $applicationProcessBundle->getApplicationProcess();
    $costItem = ApplicationCostItemFactory::createApplicationCostItem();
    $resourcesItem = ApplicationResourcesItemFactory::createApplicationResourcesItem();

    $applicationSnapshot = ApplicationSnapshotFactory::createApplicationSnapshot([
      'amount_eligible' => 1.2,
      'cost_items' => [$costItem->toArray()],
      'resources_items' => [$resourcesItem->toArray()],
      'custom_fields' => ['foo.bar' => 'baz'],
    ]);

    $this->applicationSnapshotManagerMock->method('getLastByApplicationProcessId')
      ->with($applicationProcess->getId())
      ->willReturn($applicationSnapshot);

    $this->applicationProcessManagerMock->expects(static::once())->method('update')
      ->with($applicationProcessBundle);

    $this->costItemManagerMock->expects(static::once())->method('updateAll')
      ->with($applicationProcess->getId(), [$costItem]);

    $this->resourcesItemManagerMock->expects(static::once())->method('updateAll')
      ->with($applicationProcess->getId(), [$resourcesItem]);

    $externalFile = ExternalFileFactory::create(['identifier' => 'testIdentifier']);
    $this->externalFileManagerMock->method('getFilesAttachedToSnapshot')
      ->with($applicationSnapshot->getId())
      ->willReturn([$externalFile]);
    $this->externalFileManagerMock->expects(static::once())->method('restoreFileSnapshot')
      ->with($externalFile);
    $this->externalFileManagerMock->expects(static::once())->method('deleteFiles')
      ->with($applicationProcess->getId(), ['testIdentifier']);

    $this->eventDispatcherMock->expects(static::once())->method('dispatch')
      ->with(
        ApplicationSnapshotRestoredEvent::class,
        new ApplicationSnapshotRestoredEvent($applicationProcessBundle),
      );

    $this->applicationSnapshotRestorer->restoreLastSnapshot($applicationProcessBundle);

    static::assertSame($applicationSnapshot->getStatus(), $applicationProcess->getStatus());
    static::assertSame($applicationSnapshot->getTitle(), $applicationProcess->getTitle());
    static::assertSame($applicationSnapshot->getShortDescription(), $applicationProcess->getShortDescription());
    static::assertEquals($applicationSnapshot->getStartDate(), $applicationProcess->getStartDate());
    static::assertEquals($applicationSnapshot->getEndDate(), $applicationProcess->getEndDate());
    static::assertSame($applicationSnapshot->getRequestData(), $applicationProcess->getRequestData());
    static::assertSame($applicationSnapshot->getAmountRequested(), $applicationProcess->getAmountRequested());
    static::assertSame($applicationSnapshot->getAmountEligible(), $applicationProcess->getAmountEligible());
    static::assertSame($applicationSnapshot->getIsReviewContent(), $applicationProcess->getIsReviewContent());
    static::assertSame($applicationSnapshot->getIsReviewCalculative(), $applicationProcess->getIsReviewCalculative());
    static::assertSame($applicationSnapshot->getIsEligible(), $applicationProcess->getIsEligible());
    static::assertSame('baz', $applicationProcess->get('foo.bar'));
    static::assertSame($applicationSnapshot, $applicationProcess->getRestoredSnapshot());
  }

}
