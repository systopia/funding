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
use Civi\Funding\ApplicationProcess\ApplicationProcessBundleLoader;
use Civi\Funding\ClearingProcess\ClearingProcessManager;
use Civi\Funding\ClearingProcess\ClearingProcessPermissions;
use Civi\Funding\Entity\ApplicationProcessEntityBundle;
use Civi\Funding\EntityFactory\ApplicationProcessBundleFactory;
use Civi\Funding\EntityFactory\ClearingProcessBundleFactory;
use Civi\Funding\Traits\CreateMockTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\ClearingProcess\Api4\ActionHandler\RemoteGetOrCreateActionHandler
 */
final class RemoteGetOrCreateActionHandlerTest extends TestCase {

  use CreateMockTrait;

  /**
   * @var \Civi\Funding\ApplicationProcess\ApplicationProcessBundleLoader&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $applicationProcessBundleLoaderMock;

  /**
   * @var \Civi\Funding\ClearingProcess\ClearingProcessManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $clearingProcessManagerMock;

  private RemoteGetOrCreateActionHandler $handler;

  protected function setUp(): void {
    parent::setUp();

    $this->applicationProcessBundleLoaderMock = $this->createMock(ApplicationProcessBundleLoader::class);
    $this->clearingProcessManagerMock = $this->createMock(ClearingProcessManager::class);
    $this->handler = new RemoteGetOrCreateActionHandler(
      $this->applicationProcessBundleLoaderMock,
      $this->clearingProcessManagerMock
    );
  }

  public function testInvalidApplicationProcessId(): void {
    $action = static::createApi4ActionMock(GetOrCreateAction::class);
    $action->setApplicationProcessId(12);

    $this->applicationProcessBundleLoaderMock->method('get')
      ->with(12)
      ->willReturn(NULL);

    $this->expectException(\CRM_Core_Exception::class);
    $this->expectExceptionMessage('No application process with ID 12 found');
    $this->handler->getOrCreate($action);
  }

  public function testApplicationProcessNotEligible(): void {
    $action = static::createApi4ActionMock(GetOrCreateAction::class);
    $action->setApplicationProcessId(12);

    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle(
      ['is_eligible' => FALSE]
    );
    $this->applicationProcessBundleLoaderMock->method('get')
      ->with(12)
      ->willReturn($applicationProcessBundle);

    $this->expectException(\CRM_Core_Exception::class);
    $this->expectExceptionMessage('Application process with ID 12 is not in an eligible status');
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
      ['is_eligible' => TRUE],
      ['permissions' => ['application_modify']],
    );
    $applicationProcessBundle = new ApplicationProcessEntityBundle(
      $clearingProcessBundle->getApplicationProcess(),
      $clearingProcessBundle->getFundingCase(),
      $clearingProcessBundle->getFundingCaseType(),
      $clearingProcessBundle->getFundingProgram()
    );

    $this->applicationProcessBundleLoaderMock->method('get')
      ->with(12)
      ->willReturn($applicationProcessBundle);

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
      ['is_eligible' => TRUE],
      ['permissions' => [$permission]],
    );
    $applicationProcessBundle = new ApplicationProcessEntityBundle(
      $clearingProcessBundle->getApplicationProcess(),
      $clearingProcessBundle->getFundingCase(),
      $clearingProcessBundle->getFundingCaseType(),
      $clearingProcessBundle->getFundingProgram()
    );

    $this->applicationProcessBundleLoaderMock->method('get')
      ->with(12)
      ->willReturn($applicationProcessBundle);

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
      ['is_eligible' => TRUE],
      ['permissions' => ['application_modify']],
    );
    $applicationProcessBundle = new ApplicationProcessEntityBundle(
      $clearingProcessBundle->getApplicationProcess(),
      $clearingProcessBundle->getFundingCase(),
      $clearingProcessBundle->getFundingCaseType(),
      $clearingProcessBundle->getFundingProgram()
    );

    $this->applicationProcessBundleLoaderMock->method('get')
      ->with(12)
      ->willReturn($applicationProcessBundle);

    $this->clearingProcessManagerMock->method('getByApplicationProcessId')
      ->with(12)
      ->willReturn($clearingProcessBundle->getClearingProcess());

    static::assertSame($clearingProcessBundle->getClearingProcess()->toArray(), $this->handler->getOrCreate($action));
  }

}
