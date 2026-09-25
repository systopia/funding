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

use Civi\Funding\ApplicationProcess\ApplicationProcessManager;
use Civi\Funding\ApplicationProcess\Command\ApplicationActionApplyCommand;
use Civi\Funding\ApplicationProcess\Command\ApplicationFormCommentPersistCommand;
use Civi\Funding\ApplicationProcess\Snapshot\ApplicationSnapshotRestorerInterface;
use Civi\Funding\ApplicationProcess\StatusDeterminer\ApplicationProcessStatusDeterminerInterface;
use Civi\Funding\Entity\FullApplicationProcessStatus;
use Civi\Funding\EntityFactory\ApplicationProcessBundleFactory;
use Civi\Funding\EntityFactory\ApplicationProcessFactory;
use Civi\Funding\EntityFactory\FundingCaseTypeFactory;
use Civi\Funding\FundingCaseType\MetaData\ApplicationProcessAction;
use Civi\Funding\Mock\ApplicationProcess\Form\Validation\ApplicationFormValidationResultFactory;
use Civi\Funding\Mock\FundingCaseType\MetaData\FundingCaseTypeMetaDataMock;
use Civi\Funding\Mock\FundingCaseType\MetaData\FundingCaseTypeMetaDataProviderMock;
use Civi\Funding\Mock\RequestContext\TestRequestContext;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\ApplicationProcess\Handler\ApplicationActionApplyHandler
 * @covers \Civi\Funding\ApplicationProcess\Command\ApplicationActionApplyCommand
 *
 * @phpstan-import-type applicationProcessValuesT from \Civi\Funding\EntityFactory\ApplicationProcessBundleFactory
 */
final class ApplicationActionApplyHandlerTest extends TestCase {

  private ApplicationProcessManager&MockObject $applicationProcessManagerMock;

  private ApplicationSnapshotRestorerInterface&MockObject $applicationSnapshotRestorerMock;

  private ApplicationFormCommentPersistHandlerInterface&MockObject $commentStoreHandlerMock;

  private ApplicationActionApplyHandler $handler;

  private FundingCaseTypeMetaDataMock $metaDataMock;

  private ApplicationProcessStatusDeterminerInterface&MockObject $statusDeterminerMock;

  public static function setUpBeforeClass(): void {
    parent::setUpBeforeClass();
    putenv('TIME_FUNC=frozen');
    \CRM_Utils_Time::setTime('2000-01-01 00:00:00');
  }

  protected function setUp(): void {
    parent::setUp();

    $this->applicationProcessManagerMock = $this->createMock(ApplicationProcessManager::class);
    $this->applicationSnapshotRestorerMock = $this->createMock(ApplicationSnapshotRestorerInterface::class);
    $this->commentStoreHandlerMock = $this->createMock(ApplicationFormCommentPersistHandlerInterface::class);
    $this->metaDataMock = new FundingCaseTypeMetaDataMock(FundingCaseTypeFactory::DEFAULT_NAME);
    $this->statusDeterminerMock = $this->createMock(ApplicationProcessStatusDeterminerInterface::class);
    $this->handler = new ApplicationActionApplyHandler(
      $this->applicationProcessManagerMock,
      $this->applicationSnapshotRestorerMock,
      $this->commentStoreHandlerMock,
      TestRequestContext::newInternal(123),
      new FundingCaseTypeMetaDataProviderMock($this->metaDataMock),
      $this->statusDeterminerMock,
    );
  }

  public function testHandleValid(): void {
    $command = $this->createCommand('some-action', FALSE, ['foo' => 'bar'], ['x.y' => 'z']);

    $newStatus = new FullApplicationProcessStatus('new_status', TRUE, FALSE);
    $this->statusDeterminerMock->method('getStatus')
      ->with($command->getApplicationProcess()->getFullStatus(), 'some-action')
      ->willReturn($newStatus);

    $this->applicationProcessManagerMock->expects(static::once())->method('update')
      ->with($command->getApplicationProcessBundle());

    $this->commentStoreHandlerMock->expects(static::never())->method('handle');

    $this->handler->handle($command);

    $applicationProcess = $command->getApplicationProcess();
    static::assertSame(ApplicationFormValidationResultFactory::TITLE, $applicationProcess->getTitle());
    static::assertSame(
      ApplicationFormValidationResultFactory::SHORT_DESCRIPTION,
      $applicationProcess->getShortDescription()
    );
    static::assertEquals(new \DateTime(ApplicationFormValidationResultFactory::START_DATE),
      $applicationProcess->getStartDate());
    static::assertEquals(new \DateTime(ApplicationFormValidationResultFactory::END_DATE),
      $applicationProcess->getEndDate());
    static::assertSame(
      ApplicationFormValidationResultFactory::AMOUNT_REQUESTED,
      $applicationProcess->getAmountRequested()
    );
    static::assertSame(['foo' => 'bar'], $applicationProcess->getRequestData());
    static::assertSame('new_status', $applicationProcess->getStatus());
    static::assertTrue($applicationProcess->getIsReviewCalculative());
    static::assertFalse($applicationProcess->getIsReviewContent());
    static::assertSame('z', $applicationProcess->get('x.y'));
  }

  /**
   * Test that first/last application date and first/last application contact ID are set.
   */
  public function testHandleFirstApply(): void {
    $command = $this->createCommand('apply', FALSE);
    $this->metaDataMock->addApplicationProcessAction(new ApplicationProcessAction([
      'name' => 'apply',
      'label' => 'Apply',
      'apply' => TRUE,
    ]));

    $applicationProcess = $command->getApplicationProcess();
    $newStatus = new FullApplicationProcessStatus('applied', NULL, NULL);
    $this->statusDeterminerMock->method('getStatus')
      ->with($applicationProcess->getFullStatus(), 'apply')
      ->willReturn($newStatus);

    $this->applicationProcessManagerMock->expects(static::once())->method('update')
      ->with($command->getApplicationProcessBundle());

    $this->handler->handle($command);

    static::assertEquals(
      new \DateTime(\CRM_Utils_Time::date('YmdHis')),
      $applicationProcess->getFirstApplicationDate()
    );
    static::assertSame(123, $applicationProcess->getFirstApplicationContactId());
    static::assertEquals(
      new \DateTime(\CRM_Utils_Time::date('YmdHis')),
      $applicationProcess->getLastApplicationDate()
    );
    static::assertSame(123, $applicationProcess->getLastApplicationContactId());
  }

  /**
   * Test that last application date and last application contact ID are set,
   * but not first application date and first application contact ID.
   */
  public function testHandleSecondApply(): void {
    $command = $this->createCommand('apply', FALSE, applicationProcessValues: [
      'first_application_date' => '2001-01-01 01:01:01',
      'first_application_contact_id' => 1,
    ]);
    $this->metaDataMock->addApplicationProcessAction(new ApplicationProcessAction([
      'name' => 'apply',
      'label' => 'Apply',
      'apply' => TRUE,
    ]));

    $applicationProcess = $command->getApplicationProcess();
    $newStatus = new FullApplicationProcessStatus('applied', NULL, NULL);
    $this->statusDeterminerMock->method('getStatus')
      ->with($applicationProcess->getFullStatus(), 'apply')
      ->willReturn($newStatus);

    $this->applicationProcessManagerMock->expects(static::once())->method('update')
      ->with($command->getApplicationProcessBundle());

    $this->handler->handle($command);

    // First application date and contact must not be overridden.
    static::assertEquals(
      new \DateTime('2001-01-01 01:01:01'),
      $applicationProcess->getFirstApplicationDate()
    );
    static::assertSame(1, $applicationProcess->getFirstApplicationContactId());
    static::assertEquals(
      new \DateTime(\CRM_Utils_Time::date('YmdHis')),
      $applicationProcess->getLastApplicationDate()
    );
    static::assertSame(123, $applicationProcess->getLastApplicationContactId());
  }

  public function testHandleComment(): void {
    $command = $this->createCommand(
      'some-action',
      FALSE,
      ['comment' => ['text' => 'test', 'type' => 'internal']]
    );

    $newStatus = new FullApplicationProcessStatus('new_status', TRUE, FALSE);
    $this->statusDeterminerMock->method('getStatus')->willReturn($newStatus);

    $this->applicationProcessManagerMock->expects(static::once())->method('update');

    $validationResult = $command->getValidationResult();
    assert(NULL !== $validationResult);
    $this->commentStoreHandlerMock->expects(static::once())->method('handle')
      ->with(new ApplicationFormCommentPersistCommand(
        $command->getApplicationProcessBundle(), $validationResult->getValidatedData(),
      ));

    $this->handler->handle($command);
  }

  public function testHandleRestore(): void {
    $command = $this->createCommand('withdraw-change', FALSE);
    $this->metaDataMock->addApplicationProcessAction(new ApplicationProcessAction([
      'name' => 'withdraw-change',
      'label' => 'Withdraw change',
      'restore' => TRUE,
    ]));

    $this->applicationSnapshotRestorerMock->expects(static::once())->method('restoreLastSnapshot')
      ->with($command->getApplicationProcessBundle());
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
      ->with($command->getApplicationProcessBundle());

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
    $this->metaDataMock->addApplicationProcessAction(new ApplicationProcessAction([
      'name' => 'delete',
      'label' => 'Delete',
      'delete' => TRUE,
    ]));

    $this->applicationProcessManagerMock->expects(static::once())->method('delete')
      ->with($command->getApplicationProcessBundle());

    $this->commentStoreHandlerMock->expects(static::never())->method('handle');

    $this->handler->handle($command);
  }

  /**
   * @phpstan-param array<string, mixed> $formData
   * @phpstan-param array<string, mixed> $mappedData
   * @phpstan-param applicationProcessValuesT $applicationProcessValues
   */
  private function createCommand(
    string $action,
    bool $readOnly,
    array $formData = [],
    array $mappedData = [],
    array $applicationProcessValues = []
  ): ApplicationActionApplyCommand {

    return new ApplicationActionApplyCommand(
      $action,
      ApplicationProcessBundleFactory::create($applicationProcessValues),
      ApplicationFormValidationResultFactory::createValid(
        ['_action' => $action] + $formData,
        $mappedData,
        [],
        [],
        $readOnly
      ),
    );
  }

}
