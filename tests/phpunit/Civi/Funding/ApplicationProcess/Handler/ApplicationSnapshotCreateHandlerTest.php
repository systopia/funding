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

use Civi\Funding\ApplicationProcess\ApplicationCostItemManager;
use Civi\Funding\ApplicationProcess\ApplicationExternalFileManagerInterface;
use Civi\Funding\ApplicationProcess\ApplicationProcessManager;
use Civi\Funding\ApplicationProcess\ApplicationResourcesItemManager;
use Civi\Funding\ApplicationProcess\ApplicationSnapshotManager;
use Civi\Funding\ApplicationProcess\Command\ApplicationSnapshotCreateCommand;
use Civi\Funding\Entity\ApplicationSnapshotEntity;
use Civi\Funding\EntityFactory\ApplicationCostItemFactory;
use Civi\Funding\EntityFactory\ApplicationProcessBundleFactory;
use Civi\Funding\EntityFactory\ApplicationResourcesItemFactory;
use Civi\Funding\EntityFactory\ExternalFileFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\PhpUnit\ClockMock;

/**
 * @covers \Civi\Funding\ApplicationProcess\Handler\ApplicationSnapshotCreateHandler
 */
final class ApplicationSnapshotCreateHandlerTest extends TestCase {

  /**
   * @var \Civi\Funding\ApplicationProcess\ApplicationProcessManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $applicationProcessManagerMock;

  /**
   * @var \Civi\Funding\ApplicationProcess\ApplicationSnapshotManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $applicationSnapshotManagerMock;

  /**
   * @var \Civi\Funding\ApplicationProcess\ApplicationCostItemManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $costItemManagerMock;

  /**
   * @var \Civi\Funding\ApplicationProcess\ApplicationExternalFileManagerInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $externalFileManagerMock;

  private ApplicationSnapshotCreateHandler $handler;

  /**
   * @var \Civi\Funding\ApplicationProcess\ApplicationResourcesItemManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $resourcesItemManagerMock;

  public static function setUpBeforeClass(): void {
    parent::setUpBeforeClass();
    ClockMock::register(__CLASS__);
    ClockMock::withClockMock(strtotime('2023-02-02 02:02:02'));
  }

  protected function setUp(): void {
    parent::setUp();

    $this->applicationProcessManagerMock = $this->createMock(ApplicationProcessManager::class);
    $this->applicationSnapshotManagerMock = $this->createMock(ApplicationSnapshotManager::class);
    $this->costItemManagerMock = $this->createMock(ApplicationCostItemManager::class);
    $this->externalFileManagerMock = $this->createMock(ApplicationExternalFileManagerInterface::class);
    $this->resourcesItemManagerMock = $this->createMock(ApplicationResourcesItemManager::class);
    $this->handler = new ApplicationSnapshotCreateHandler(
      $this->applicationProcessManagerMock,
      $this->applicationSnapshotManagerMock,
      $this->costItemManagerMock,
      $this->externalFileManagerMock,
      $this->resourcesItemManagerMock
    );
  }

  public function testHandle(): void {
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle([
      'is_eligible' => TRUE,
    ]);
    $applicationProcess = $applicationProcessBundle->getApplicationProcess();

    $this->applicationProcessManagerMock->method('getCustomFields')
      ->with($applicationProcess)
      ->willReturn(['foo.bar' => 'baz']);

    $costItem = ApplicationCostItemFactory::createApplicationCostItem(['id' => 11]);
    $this->costItemManagerMock->method('getByApplicationProcessId')
      ->with($applicationProcess->getId())
      ->willReturn([$costItem]);
    $costItemData = $costItem->toArray();
    unset($costItemData['id']);

    $resourcesItem = ApplicationResourcesItemFactory::createApplicationResourcesItem(['id' => 22]);
    $this->resourcesItemManagerMock->method('getByApplicationProcessId')
      ->with($applicationProcess->getId())
      ->willReturn([$resourcesItem]);
    $resourcesItemData = $resourcesItem->toArray();
    unset($resourcesItemData['id']);

    $this->applicationSnapshotManagerMock->expects(static::once())->method('add')
      ->with(static::callback(function (ApplicationSnapshotEntity $applicationSnapshot)
      use ($applicationProcess, $costItemData, $resourcesItemData) {
        static::assertSame($applicationProcess->getId(), $applicationSnapshot->getApplicationProcessId());
        static::assertSame($applicationProcess->getStatus(), $applicationSnapshot->getStatus());
        static::assertEquals(new \DateTime('2023-02-02 02:02:02'), $applicationSnapshot->getCreationDate());
        static::assertSame($applicationProcess->getTitle(), $applicationSnapshot->getTitle());
        static::assertSame($applicationProcess->getShortDescription(), $applicationSnapshot->getShortDescription());
        static::assertEquals($applicationProcess->getStartDate(), $applicationSnapshot->getStartDate());
        static::assertEquals($applicationProcess->getEndDate(), $applicationSnapshot->getEndDate());
        static::assertSame($applicationProcess->getRequestData(), $applicationSnapshot->getRequestData());
        static::assertSame([$costItemData], $applicationSnapshot->getCostItems());
        static::assertSame([$resourcesItemData], $applicationSnapshot->getResourcesItems());
        static::assertSame($applicationProcess->getAmountRequested(), $applicationSnapshot->getAmountRequested());
        static::assertSame($applicationProcess->getIsReviewContent(), $applicationSnapshot->getIsReviewContent());
        static::assertSame(
          $applicationProcess->getIsReviewCalculative(),
          $applicationSnapshot->getIsReviewCalculative()
        );
        static::assertSame($applicationProcess->getIsEligible(), $applicationSnapshot->getIsEligible());
        static::assertSame(['foo.bar' => 'baz'], $applicationSnapshot->getCustomFields());

        $applicationSnapshot->setValues($applicationSnapshot->toArray() + ['id' => 123]);

        return TRUE;
      }));

    $externalFile = ExternalFileFactory::create();
    $this->externalFileManagerMock->method('getFiles')
      ->with($applicationProcess->getId())
      ->willReturn([$externalFile]);
    $this->externalFileManagerMock->expects(static::once())->method('attachFileToSnapshot')
      ->with($externalFile, 123);

    $this->handler->handle(new ApplicationSnapshotCreateCommand(11, $applicationProcessBundle));
  }

}
