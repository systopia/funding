<?php
/*
 * Copyright (C) 2022 SYSTOPIA GmbH
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

use Civi\Funding\ApplicationProcess\ActionStatusInfo\DefaultApplicationProcessActionStatusInfo;
use Civi\Funding\ApplicationProcess\ActionStatusInfo\ReworkPossibleApplicationProcessActionStatusInfo;
use Civi\Funding\ApplicationProcess\ApplicationProcessManager;
use Civi\Funding\ApplicationProcess\Command\ApplicationActionApplyCommand;
use Civi\Funding\ApplicationProcess\Command\ApplicationFormCommentPersistCommand;
use Civi\Funding\ApplicationProcess\Snapshot\ApplicationSnapshotRestorerInterface;
use Civi\Funding\ApplicationProcess\StatusDeterminer\ApplicationProcessStatusDeterminerInterface;
use Civi\Funding\Entity\FullApplicationProcessStatus;
use Civi\Funding\EntityFactory\ApplicationProcessBundleFactory;
use Civi\Funding\EntityFactory\ApplicationProcessFactory;
use Civi\Funding\Form\Application\ApplicationValidationResult;
use Civi\Funding\Mock\Form\ValidatedApplicationDataMock;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\ApplicationProcess\Handler\ApplicationActionApplyHandler
 * @covers \Civi\Funding\ApplicationProcess\Command\ApplicationActionApplyCommand
 */
final class ApplicationActionApplyHandlerTest extends TestCase {

  /**
   * @var \Civi\Funding\ApplicationProcess\ApplicationProcessManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $applicationProcessManagerMock;

  /**
   * @var \Civi\Funding\ApplicationProcess\Snapshot\ApplicationSnapshotRestorerInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $applicationSnapshotRestorerMock;

  /**
   * @var \Civi\Funding\ApplicationProcess\Handler\ApplicationFormCommentPersistHandlerInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $commentStoreHandlerMock;

  private ApplicationActionApplyHandler $handler;

  /**
   * @var \Civi\Funding\ApplicationProcess\StatusDeterminer\ApplicationProcessStatusDeterminerInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $statusDeterminerMock;

  protected function setUp(): void {
    parent::setUp();
    $info = new ReworkPossibleApplicationProcessActionStatusInfo(
      new DefaultApplicationProcessActionStatusInfo()
    );
    $this->applicationProcessManagerMock = $this->createMock(ApplicationProcessManager::class);
    $this->applicationSnapshotRestorerMock = $this->createMock(ApplicationSnapshotRestorerInterface::class);
    $this->commentStoreHandlerMock = $this->createMock(ApplicationFormCommentPersistHandlerInterface::class);
    $this->statusDeterminerMock = $this->createMock(ApplicationProcessStatusDeterminerInterface::class);
    $this->handler = new ApplicationActionApplyHandler(
      $this->applicationProcessManagerMock,
      $this->applicationSnapshotRestorerMock,
      $this->commentStoreHandlerMock,
      $info,
      $this->statusDeterminerMock,
    );
  }

  public function testHandleValid(): void {
    $command = $this->createCommand(ValidatedApplicationDataMock::ACTION, FALSE, [], ['foo' => 'bar']);

    $newStatus = new FullApplicationProcessStatus('new_status', TRUE, FALSE);
    $this->statusDeterminerMock->method('getStatus')
      ->with($command->getApplicationProcess()->getFullStatus(), ValidatedApplicationDataMock::ACTION)
      ->willReturn($newStatus);

    $this->applicationProcessManagerMock->expects(static::once())->method('update')
      ->with($command->getContactId(), $command->getApplicationProcessBundle());

    $this->commentStoreHandlerMock->expects(static::never())->method('handle');

    $this->handler->handle($command);

    $applicationProcess = $command->getApplicationProcess();
    static::assertSame(ValidatedApplicationDataMock::TITLE, $applicationProcess->getTitle());
    static::assertSame(ValidatedApplicationDataMock::SHORT_DESCRIPTION, $applicationProcess->getShortDescription());
    static::assertEquals(new \DateTime(ValidatedApplicationDataMock::START_DATE),
      $applicationProcess->getStartDate());
    static::assertEquals(new \DateTime(ValidatedApplicationDataMock::END_DATE),
      $applicationProcess->getEndDate());
    static::assertSame(ValidatedApplicationDataMock::AMOUNT_REQUESTED, $applicationProcess->getAmountRequested());
    static::assertSame(['foo' => 'bar'], $applicationProcess->getRequestData());
    static::assertSame('new_status', $applicationProcess->getStatus());
    static::assertTrue($applicationProcess->getIsReviewCalculative());
    static::assertFalse($applicationProcess->getIsReviewContent());
  }

  public function testHandleComment(): void {
    $command = $this->createCommand(
      ValidatedApplicationDataMock::ACTION,
      FALSE,
      ['comment' => ['text' => 'test', 'type' => 'internal']]
    );

    $newStatus = new FullApplicationProcessStatus('new_status', TRUE, FALSE);
    $this->statusDeterminerMock->method('getStatus')->willReturn($newStatus);

    $this->applicationProcessManagerMock->expects(static::once())->method('update');

    $this->commentStoreHandlerMock->expects(static::once())->method('handle')
      ->with(new ApplicationFormCommentPersistCommand(
        $command->getContactId(),
        $command->getApplicationProcess(),
        $command->getFundingCase(),
        $command->getFundingCaseType(),
        $command->getFundingProgram(),
        // @phpstan-ignore-next-line
        $command->getValidationResult()->getValidatedData(),
      ));

    $this->handler->handle($command);
  }

  public function testHandleRestore(): void {
    $command = $this->createCommand('withdraw-change', FALSE);

    $this->applicationSnapshotRestorerMock->expects(static::once())->method('restoreLastSnapshot')
      ->with($command->getContactId(), $command->getApplicationProcessBundle());
    $this->applicationProcessManagerMock->expects(static::never())->method('update');

    $this->handler->handle($command);
  }

  public function testHandleValidReadOnly(): void {
    $command = $this->createCommand('modify', TRUE);

    $newStatus = new FullApplicationProcessStatus('new_status', TRUE, FALSE);
    $this->statusDeterminerMock->method('getStatus')
      ->with($command->getApplicationProcess()->getFullStatus(), 'modify')
      ->willReturn($newStatus);

    $this->applicationProcessManagerMock->expects(static::once())->method('update')
      ->with($command->getContactId(), $command->getApplicationProcessBundle());

    $this->commentStoreHandlerMock->expects(static::never())->method('handle');

    $this->handler->handle($command);

    // only status should be changed because validation result contains read only
    $expectedApplicationProcess = ApplicationProcessFactory::createApplicationProcess([
      'status' => 'new_status',
      'is_review_calculative' => TRUE,
      'is_review_content' => FALSE,
    ]);
    static::assertEquals($expectedApplicationProcess, $command->getApplicationProcess());
  }

  public function testHandleValidDelete(): void {
    $command = $this->createCommand('delete', FALSE);

    $this->applicationProcessManagerMock->expects(static::once())->method('delete')
      ->with($command->getApplicationProcessBundle());

    $this->commentStoreHandlerMock->expects(static::never())->method('handle');

    $this->handler->handle($command);
  }

  /**
   * @phpstan-param array{
   *    action?: string,
   *    title?: string,
   *    shortDescription?: string,
   *    recipientContactId?: int,
   *    startDate?: string,
   *    endDate?: string,
   *    amountRequested?: float,
   *    comment?: array{text: string, type: string},
   *  } $data
   * @phpstan-param array<string, mixed> $applicationData
   */
  private function createCommand(
    string $action,
    bool $readOnly,
    array $data = [],
    array $applicationData = []
  ): ApplicationActionApplyCommand {

    return new ApplicationActionApplyCommand(
      1,
      $action,
      ApplicationProcessBundleFactory::createApplicationProcessBundle(),
      ApplicationValidationResult::newValid(
        new ValidatedApplicationDataMock($applicationData, ['_action' => $action] + $data),
        $readOnly
      ),
    );
  }

}
