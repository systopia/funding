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

use Civi\API\Exception\UnauthorizedException;
use Civi\Funding\ApplicationProcess\ActionsDeterminer\ApplicationProcessActionsDeterminerInterface;
use Civi\Funding\ApplicationProcess\ApplicationProcessManager;
use Civi\Funding\ApplicationProcess\Command\ApplicationDeleteCommand;
use Civi\Funding\Entity\FullApplicationProcessStatus;
use Civi\Funding\EntityFactory\ApplicationProcessBundleFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\ApplicationProcess\Handler\ApplicationDeleteHandler
 */
final class ApplicationDeleteHandlerTest extends TestCase {

  /**
   * @var \Civi\Funding\ApplicationProcess\ActionsDeterminer\ApplicationProcessActionsDeterminerInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $actionsDeterminerMock;

  /**
   * @var \Civi\Funding\ApplicationProcess\ApplicationProcessManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $applicationProcessManagerMock;

  private ApplicationDeleteHandler $handler;

  protected function setUp(): void {
    parent::setUp();
    $this->actionsDeterminerMock = $this->createMock(ApplicationProcessActionsDeterminerInterface::class);
    $this->applicationProcessManagerMock = $this->createMock(ApplicationProcessManager::class);
    $this->handler = new ApplicationDeleteHandler(
      $this->actionsDeterminerMock,
      $this->applicationProcessManagerMock,
    );
  }

  public function testHandle(): void {
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle();
    $statusList = [23 => new FullApplicationProcessStatus('status', NULL, NULL)];
    $this->actionsDeterminerMock->method('isActionAllowed')
      ->with(
        'delete',
        $applicationProcessBundle,
        $statusList
      )->willReturn(TRUE);

    $this->applicationProcessManagerMock->expects(static::once())->method('delete')
      ->with($applicationProcessBundle);

    $this->handler->handle(new ApplicationDeleteCommand($applicationProcessBundle, $statusList));
  }

  public function testHandlePermissionMissing(): void {
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle();
    $statusList = [23 => new FullApplicationProcessStatus('status', NULL, NULL)];
    $this->actionsDeterminerMock->method('isActionAllowed')
      ->with(
        'delete',
        $applicationProcessBundle,
        $statusList
      )->willReturn(FALSE);

    $this->expectException(UnauthorizedException::class);
    $this->expectExceptionMessage('Permission to delete application is missing.');

    $this->handler->handle(new ApplicationDeleteCommand($applicationProcessBundle, $statusList));
  }

}
