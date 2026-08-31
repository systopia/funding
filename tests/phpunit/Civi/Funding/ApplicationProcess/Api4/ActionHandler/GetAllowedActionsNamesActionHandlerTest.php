<?php
/*
 * Copyright (C) 2026 SYSTOPIA GmbH
 *
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License as published by the Free
 * Software Foundation, either version 3 of the License, or (at your option) any
 * later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types = 1);

namespace Civi\Funding\ApplicationProcess\Api4\ActionHandler;

use Civi\Funding\Api4\Action\FundingApplicationProcess\GetAllowedActionNamesAction;
use Civi\Funding\ApplicationProcess\ActionsDeterminer\ApplicationProcessActionsDeterminerInterface;
use Civi\Funding\ApplicationProcess\ApplicationProcessManager;
use Civi\Funding\Entity\FullApplicationProcessStatus;
use Civi\Funding\EntityFactory\ApplicationProcessBundleFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\ApplicationProcess\Api4\ActionHandler\GetAllowedActionsNamesActionHandler
 */
final class GetAllowedActionsNamesActionHandlerTest extends TestCase {

  private GetAllowedActionsNamesActionHandler $actionHandler;

  private ApplicationProcessActionsDeterminerInterface&MockObject $actionsDeterminerMock;

  private ApplicationProcessManager&MockObject $applicationProcessManagerMock;

  protected function setUp(): void {
    parent::setUp();
    $this->actionsDeterminerMock = $this->createMock(ApplicationProcessActionsDeterminerInterface::class);
    $this->applicationProcessManagerMock = $this->createMock(ApplicationProcessManager::class);
    $this->actionHandler = new GetAllowedActionsNamesActionHandler(
      $this->actionsDeterminerMock,
      $this->applicationProcessManagerMock
    );
  }

  public function testGetAllowedActionNames(): void {
    $applicationProcessBundle = ApplicationProcessBundleFactory::create(['id' => 12]);
    $this->applicationProcessManagerMock->method('getBundle')
      ->with(12)
      ->willReturn($applicationProcessBundle);
    $statusList = [23 => new FullApplicationProcessStatus('status', NULL, NULL)];
    $this->applicationProcessManagerMock->method('getStatusList')
      ->with($applicationProcessBundle)
      ->willReturn($statusList);
    $this->actionsDeterminerMock->method('getActions')
      ->with($applicationProcessBundle, $statusList)
      ->willReturn(['test_action']);

    $action = (new GetAllowedActionNamesAction())
      ->setIds([12]);

    static::assertEquals(
      [12 => ['test_action']],
      $this->actionHandler->getAllowedActionNames($action)
    );
  }

  public function testUnknownId(): void {
    $this->applicationProcessManagerMock->method('getBundle')
      ->with(12)
      ->willReturn(NULL);

    $action = (new GetAllowedActionNamesAction())
      ->setIds([12]);

    static::assertEquals(
      [12 => []],
      $this->actionHandler->getAllowedActionNames($action)
    );
  }

}
