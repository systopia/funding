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

use Civi\Funding\ApplicationProcess\ApplicationResourcesItemManager;
use Civi\Funding\ApplicationProcess\ApplicationResourcesItemsFactoryInterface;
use Civi\Funding\ApplicationProcess\Command\ApplicationResourcesItemsPersistCommand;
use Civi\Funding\EntityFactory\ApplicationResourcesItemFactory;
use Civi\Funding\EntityFactory\ApplicationProcessFactory;
use Civi\Funding\EntityFactory\FundingCaseFactory;
use Civi\Funding\EntityFactory\FundingCaseTypeFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\ApplicationProcess\Handler\ApplicationResourcesItemsPersistHandler
 */
final class ApplicationResourcesItemsPersistHandlerTest extends TestCase {

  /**
   * @var \Civi\Funding\ApplicationProcess\ApplicationResourcesItemsFactoryInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $resourcesItemsFactoryMock;

  /**
   * @var \Civi\Funding\ApplicationProcess\ApplicationResourcesItemManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $resourcesItemManagerMock;

  private ApplicationResourcesItemsPersistHandler $handler;

  protected function setUp(): void {
    parent::setUp();
    $this->resourcesItemsFactoryMock = $this->createMock(ApplicationResourcesItemsFactoryInterface::class);
    $this->resourcesItemManagerMock = $this->createMock(ApplicationResourcesItemManager::class);
    $this->handler = new ApplicationResourcesItemsPersistHandler(
      $this->resourcesItemsFactoryMock,
      $this->resourcesItemManagerMock,
    );
  }

  public function testHandleNew(): void {
    $applicationProcess = ApplicationProcessFactory::createApplicationProcess(['request_data' => ['foo' => 'bar']]);
    $fundingCase = FundingCaseFactory::createFundingCase();
    $fundingCaseType = FundingCaseTypeFactory::createFundingCaseType();

    $resourcesItems = [ApplicationResourcesItemFactory::createApplicationResourcesItem()];
    $this->resourcesItemsFactoryMock->expects(static::once())->method('createItems')
      ->with($applicationProcess)
      ->willReturn($resourcesItems);
    $this->resourcesItemManagerMock->expects(static::once())->method('updateAll')
      ->with($applicationProcess->getId(), $resourcesItems);

    $this->handler->handle(new ApplicationResourcesItemsPersistCommand(
      $applicationProcess,
      $fundingCase,
      $fundingCaseType,
      NULL,
    ));
  }

  public function testHandleUpdate(): void {
    $previousApplicationProcess = ApplicationProcessFactory::createApplicationProcess(
      ['request_data' => ['foo' => 'bar']]
    );
    $applicationProcess = ApplicationProcessFactory::createApplicationProcess(['request_data' => ['baz' => 'bar']]);
    $fundingCase = FundingCaseFactory::createFundingCase();
    $fundingCaseType = FundingCaseTypeFactory::createFundingCaseType();

    $resourcesItems = [ApplicationResourcesItemFactory::createApplicationResourcesItem()];
    $this->resourcesItemsFactoryMock->expects(static::once())->method('areResourcesItemsChanged')
      ->with(['baz' => 'bar'], ['foo' => 'bar'])
      ->willReturn(TRUE);
    $this->resourcesItemsFactoryMock->expects(static::once())->method('createItems')
      ->with($applicationProcess)
      ->willReturn($resourcesItems);
    $this->resourcesItemManagerMock->expects(static::once())->method('updateAll')
      ->with($applicationProcess->getId(), $resourcesItems);

    $this->handler->handle(new ApplicationResourcesItemsPersistCommand(
      $applicationProcess,
      $fundingCase,
      $fundingCaseType,
      $previousApplicationProcess,
    ));
  }

  public function testHandleUpdateResourcesItemUnchanged(): void {
    $previousApplicationProcess = ApplicationProcessFactory::createApplicationProcess(
      ['request_data' => ['foo' => 'bar']]
    );
    $applicationProcess = ApplicationProcessFactory::createApplicationProcess(['request_data' => ['baz' => 'bar']]);
    $fundingCase = FundingCaseFactory::createFundingCase();
    $fundingCaseType = FundingCaseTypeFactory::createFundingCaseType();

    $this->resourcesItemsFactoryMock->expects(static::once())->method('areResourcesItemsChanged')
      ->with(['baz' => 'bar'], ['foo' => 'bar'])
      ->willReturn(FALSE);
    $this->resourcesItemsFactoryMock->expects(static::never())->method('createItems');
    $this->resourcesItemManagerMock->expects(static::never())->method('updateAll');

    $this->handler->handle(new ApplicationResourcesItemsPersistCommand(
      $applicationProcess,
      $fundingCase,
      $fundingCaseType,
      $previousApplicationProcess,
    ));
  }

}
