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

namespace Civi\Funding\ClearingProcess\Api4\ActionHandler;

use Civi\API\Exception\UnauthorizedException;
use Civi\Funding\Api4\Action\Remote\FundingClearingProcess\GetOrCreateAction;
use Civi\Funding\ApplicationProcess\ApplicationProcessManager;
use Civi\Funding\ClearingProcess\ClearingProcessManager;
use Civi\Funding\ClearingProcess\ClearingProcessPermissions;
use Civi\Funding\Entity\ApplicationProcessEntityBundle;
use Civi\Funding\EntityFactory\ApplicationProcessBundleFactory;
use Civi\Funding\EntityFactory\ClearingProcessBundleFactory;
use Civi\Funding\FundingCaseType\MetaData\ApplicationProcessStatus;
use Civi\Funding\Mock\FundingCaseType\MetaData\FundingCaseTypeMetaDataMock;
use Civi\Funding\Mock\FundingCaseType\MetaData\FundingCaseTypeMetaDataProviderMock;
use Civi\Funding\Traits\CreateMockTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\ClearingProcess\Api4\ActionHandler\RemoteGetOrCreateActionHandler
 */
final class RemoteGetOrCreateActionHandlerTest extends TestCase {

  use CreateMockTrait;

  private ApplicationProcessManager&MockObject $applicationProcessManagerMock;

  private ClearingProcessManager&MockObject $clearingProcessManagerMock;

  private RemoteGetOrCreateActionHandler $handler;

  private FundingCaseTypeMetaDataMock $metaDataMock;

  protected function setUp(): void {
    parent::setUp();

    $this->metaDataMock = new FundingCaseTypeMetaDataMock();

    $this->applicationProcessManagerMock = $this->createMock(ApplicationProcessManager::class);
    $this->clearingProcessManagerMock = $this->createMock(ClearingProcessManager::class);
    $this->handler = new RemoteGetOrCreateActionHandler(
      $this->applicationProcessManagerMock,
      $this->clearingProcessManagerMock,
      new FundingCaseTypeMetaDataProviderMock($this->metaDataMock),
    );
  }

  public function testInvalidApplicationProcessId(): void {
    $action = static::createApi4ActionMock(GetOrCreateAction::class);
    $action->setApplicationProcessId(12);

    $this->applicationProcessManagerMock->method('getBundle')
      ->with(12)
      ->willReturn(NULL);

    $this->expectException(\CRM_Core_Exception::class);
    $this->expectExceptionMessage('No application process with ID 12 found');
    $this->handler->getOrCreate($action);
  }

  public function testApplicationProcessNotClearable(): void {
    $action = static::createApi4ActionMock(GetOrCreateAction::class);
    $action->setApplicationProcessId(12);

    $applicationProcessBundle = ApplicationProcessBundleFactory::create(['status' => 'test']);
    $this->applicationProcessManagerMock->method('getBundle')
      ->with(12)
      ->willReturn($applicationProcessBundle);

    $this->metaDataMock->addApplicationProcessStatus(new ApplicationProcessStatus([
      'name' => 'test',
      'label' => 'Test',
      'clearingAvailable' => FALSE,
    ]));

    $this->expectException(\CRM_Core_Exception::class);
    $this->expectExceptionMessage('Application process with ID 12 is currently not clearable');
    $this->handler->getOrCreate($action);
  }

  public function testCreateNoPermission(): void {
    $action = static::createApi4ActionMock(GetOrCreateAction::class);
    $action->setApplicationProcessId(12);

    $clearingProcessBundle = ClearingProcessBundleFactory::create(
      [
        'status' => 'not-started',
        'creation_date' => NULL,
        'modification_date' => NULL,
      ],
      ['status' => 'test'],
      ['permissions' => ['application_modify']],
    );
    $applicationProcessBundle = new ApplicationProcessEntityBundle(
      $clearingProcessBundle->getApplicationProcess(),
      $clearingProcessBundle->getFundingCase(),
      $clearingProcessBundle->getFundingCaseType(),
      $clearingProcessBundle->getFundingProgram()
    );

    $this->applicationProcessManagerMock->method('getBundle')
      ->with(12)
      ->willReturn($applicationProcessBundle);

    $this->metaDataMock->addApplicationProcessStatus(new ApplicationProcessStatus([
      'name' => 'test',
      'label' => 'Test',
      'clearingAvailable' => TRUE,
    ]));

    $this->clearingProcessManagerMock->method('getByApplicationProcessId')
      ->with(12)
      ->willReturn($clearingProcessBundle->getClearingProcess());

    $this->expectException(UnauthorizedException::class);
    $this->expectExceptionMessage('Permission to start clearing for application process with ID 12 is missing');
    $this->handler->getOrCreate($action);
  }

  /**
   * @dataProvider provideClearingPermissions
   */
  public function testCreate(string $permission): void {
    $action = static::createApi4ActionMock(GetOrCreateAction::class);
    $action->setApplicationProcessId(12);

    $clearingProcessBundle = ClearingProcessBundleFactory::create(
      [
        'status' => 'not-started',
        'creation_date' => NULL,
        'modification_date' => NULL,
      ],
      ['status' => 'test'],
      ['permissions' => [$permission]],
    );
    $applicationProcessBundle = new ApplicationProcessEntityBundle(
      $clearingProcessBundle->getApplicationProcess(),
      $clearingProcessBundle->getFundingCase(),
      $clearingProcessBundle->getFundingCaseType(),
      $clearingProcessBundle->getFundingProgram()
    );

    $this->applicationProcessManagerMock->method('getBundle')
      ->with(12)
      ->willReturn($applicationProcessBundle);

    $this->metaDataMock->addApplicationProcessStatus(new ApplicationProcessStatus([
      'name' => 'test',
      'label' => 'Test',
      'clearingAvailable' => TRUE,
    ]));

    $this->clearingProcessManagerMock->method('getByApplicationProcessId')
      ->with(12)
      ->willReturn($clearingProcessBundle->getClearingProcess());

    $this->clearingProcessManagerMock->expects(static::once())->method('start')
      ->with($clearingProcessBundle);

    static::assertSame($clearingProcessBundle->getClearingProcess()->toArray(), $this->handler->getOrCreate($action));
  }

  /**
   * @phpstan-return iterable<array{string}>
   */
  public function provideClearingPermissions(): iterable {
    yield [ClearingProcessPermissions::CLEARING_APPLY];
    yield [ClearingProcessPermissions::CLEARING_MODIFY];
  }

  public function testGet(): void {
    $action = static::createApi4ActionMock(GetOrCreateAction::class);
    $action->setApplicationProcessId(12);

    $clearingProcessBundle = ClearingProcessBundleFactory::create(
      [
        'status' => 'draft',
        'creation_date' => '2024-11-11 01:02:03',
        'modification_date' => '2024-11-11 01:02:03',
      ],
      ['status' => 'test'],
      ['permissions' => ['application_modify']],
    );
    $applicationProcessBundle = new ApplicationProcessEntityBundle(
      $clearingProcessBundle->getApplicationProcess(),
      $clearingProcessBundle->getFundingCase(),
      $clearingProcessBundle->getFundingCaseType(),
      $clearingProcessBundle->getFundingProgram()
    );

    $this->applicationProcessManagerMock->method('getBundle')
      ->with(12)
      ->willReturn($applicationProcessBundle);

    $this->metaDataMock->addApplicationProcessStatus(new ApplicationProcessStatus([
      'name' => 'test',
      'label' => 'Test',
      'clearingAvailable' => TRUE,
    ]));

    $this->clearingProcessManagerMock->method('getByApplicationProcessId')
      ->with(12)
      ->willReturn($clearingProcessBundle->getClearingProcess());

    static::assertSame($clearingProcessBundle->getClearingProcess()->toArray(), $this->handler->getOrCreate($action));
  }

}
