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
use Civi\Funding\ApplicationProcess\ApplicationCostItemsFactoryInterface;
use Civi\Funding\ApplicationProcess\Command\ApplicationCostItemsPersistCommand;
use Civi\Funding\EntityFactory\ApplicationCostItemFactory;
use Civi\Funding\EntityFactory\ApplicationProcessFactory;
use Civi\Funding\EntityFactory\FundingCaseFactory;
use Civi\Funding\EntityFactory\FundingCaseTypeFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\ApplicationProcess\Handler\ApplicationCostItemsPersistHandler
 */
final class ApplicationCostItemsPersistHandlerTest extends TestCase {

  /**
   * @var \Civi\Funding\ApplicationProcess\ApplicationCostItemsFactoryInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $costItemsFactoryMock;

  /**
   * @var \Civi\Funding\ApplicationProcess\ApplicationCostItemManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $costItemManagerMock;

  private ApplicationCostItemsPersistHandler $handler;

  protected function setUp(): void {
    parent::setUp();
    $this->costItemsFactoryMock = $this->createMock(ApplicationCostItemsFactoryInterface::class);
    $this->costItemManagerMock = $this->createMock(ApplicationCostItemManager::class);
    $this->handler = new ApplicationCostItemsPersistHandler(
      $this->costItemsFactoryMock,
      $this->costItemManagerMock,
    );
  }

  public function testHandleNew(): void {
    $applicationProcess = ApplicationProcessFactory::createApplicationProcess(['request_data' => ['foo' => 'bar']]);
    $fundingCase = FundingCaseFactory::createFundingCase();
    $fundingCaseType = FundingCaseTypeFactory::createFundingCaseType();

    $costItems = [ApplicationCostItemFactory::createApplicationCostItem()];
    $this->costItemsFactoryMock->expects(static::once())->method('createItems')
      ->with($applicationProcess)
      ->willReturn($costItems);
    $this->costItemManagerMock->expects(static::once())->method('updateAll')
      ->with($applicationProcess->getId(), $costItems);

    $this->handler->handle(new ApplicationCostItemsPersistCommand(
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

    $costItems = [ApplicationCostItemFactory::createApplicationCostItem()];
    $this->costItemsFactoryMock->expects(static::once())->method('areCostItemsChanged')
      ->with(['baz' => 'bar'], ['foo' => 'bar'])
      ->willReturn(TRUE);
    $this->costItemsFactoryMock->expects(static::once())->method('createItems')
      ->with($applicationProcess)
      ->willReturn($costItems);
    $this->costItemManagerMock->expects(static::once())->method('updateAll')
      ->with($applicationProcess->getId(), $costItems);

    $this->handler->handle(new ApplicationCostItemsPersistCommand(
      $applicationProcess,
      $fundingCase,
      $fundingCaseType,
      $previousApplicationProcess,
    ));
  }

  public function testHandleUpdateCostItemUnchanged(): void {
    $previousApplicationProcess = ApplicationProcessFactory::createApplicationProcess(
      ['request_data' => ['foo' => 'bar']]
    );
    $applicationProcess = ApplicationProcessFactory::createApplicationProcess(['request_data' => ['baz' => 'bar']]);
    $fundingCase = FundingCaseFactory::createFundingCase();
    $fundingCaseType = FundingCaseTypeFactory::createFundingCaseType();

    $this->costItemsFactoryMock->expects(static::once())->method('areCostItemsChanged')
      ->with(['baz' => 'bar'], ['foo' => 'bar'])
      ->willReturn(FALSE);
    $this->costItemsFactoryMock->expects(static::never())->method('createItems');
    $this->costItemManagerMock->expects(static::never())->method('updateAll');

    $this->handler->handle(new ApplicationCostItemsPersistCommand(
      $applicationProcess,
      $fundingCase,
      $fundingCaseType,
      $previousApplicationProcess,
    ));
  }

}
