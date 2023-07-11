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
use Civi\Funding\ApplicationProcess\ApplicationSnapshotManager;
use Civi\Funding\ApplicationProcess\Command\ApplicationSnapshotCreateCommand;
use Civi\Funding\Entity\ApplicationSnapshotEntity;
use Civi\Funding\EntityFactory\ApplicationProcessBundleFactory;
use Civi\Funding\EntityFactory\ExternalFileFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\PhpUnit\ClockMock;

/**
 * @covers \Civi\Funding\ApplicationProcess\Handler\ApplicationSnapshotCreateHandler
 */
final class ApplicationSnapshotCreateHandlerTest extends TestCase {

  /**
   * @var \Civi\Funding\ApplicationProcess\ApplicationSnapshotManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $applicationSnapshotManagerMock;

  /**
   * @var \Civi\Funding\ApplicationProcess\ApplicationExternalFileManagerInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $externalFileManagerMock;

  private ApplicationSnapshotCreateHandler $handler;

  public static function setUpBeforeClass(): void {
    parent::setUpBeforeClass();
    ClockMock::register(__CLASS__);
    ClockMock::withClockMock(strtotime('2023-02-02 02:02:02'));
  }

  protected function setUp(): void {
    parent::setUp();
    $this->applicationSnapshotManagerMock = $this->createMock(ApplicationSnapshotManager::class);
    $this->externalFileManagerMock = $this->createMock(ApplicationExternalFileManagerInterface::class);
    $this->handler = new ApplicationSnapshotCreateHandler(
      $this->applicationSnapshotManagerMock,
      $this->externalFileManagerMock,
    );
  }

  public function testHandle(): void {
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle([
      'is_eligible' => TRUE,
    ]);
    $applicationProcess = $applicationProcessBundle->getApplicationProcess();
    $this->applicationSnapshotManagerMock->expects(static::once())->method('add')
      ->with(static::callback(function (ApplicationSnapshotEntity $applicationSnapshot) use ($applicationProcess) {
        static::assertSame($applicationProcess->getId(), $applicationSnapshot->getApplicationProcessId());
        static::assertSame($applicationProcess->getStatus(), $applicationSnapshot->getStatus());
        static::assertEquals(new \DateTime('2023-02-02 02:02:02'), $applicationSnapshot->getCreationDate());
        static::assertSame($applicationProcess->getTitle(), $applicationSnapshot->getTitle());
        static::assertSame($applicationProcess->getShortDescription(), $applicationSnapshot->getShortDescription());
        static::assertEquals($applicationProcess->getStartDate(), $applicationSnapshot->getStartDate());
        static::assertEquals($applicationProcess->getEndDate(), $applicationSnapshot->getEndDate());
        static::assertSame($applicationProcess->getRequestData(), $applicationSnapshot->getRequestData());
        static::assertSame($applicationProcess->getAmountRequested(), $applicationSnapshot->getAmountRequested());
        static::assertSame($applicationProcess->getIsReviewContent(), $applicationSnapshot->getIsReviewContent());
        static::assertSame(
          $applicationProcess->getIsReviewCalculative(),
          $applicationSnapshot->getIsReviewCalculative()
        );
        static::assertSame($applicationProcess->getIsEligible(), $applicationSnapshot->getIsEligible());

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
