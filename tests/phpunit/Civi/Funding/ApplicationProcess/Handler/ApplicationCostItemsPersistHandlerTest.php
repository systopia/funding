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
use Civi\Funding\ApplicationProcess\Command\ApplicationCostItemsPersistCommand;
use Civi\Funding\ApplicationProcess\JsonSchema\CostItem\CostItemData;
use Civi\Funding\EntityFactory\ApplicationCostItemFactory;
use Civi\Funding\EntityFactory\ApplicationProcessBundleFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\ApplicationProcess\Handler\ApplicationCostItemsPersistHandler
 */
final class ApplicationCostItemsPersistHandlerTest extends TestCase {

  /**
   * @var \Civi\Funding\ApplicationProcess\ApplicationCostItemManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $costItemManagerMock;

  private ApplicationCostItemsPersistHandler $handler;

  protected function setUp(): void {
    parent::setUp();
    $this->costItemManagerMock = $this->createMock(ApplicationCostItemManager::class);
    $this->handler = new ApplicationCostItemsPersistHandler(
      $this->costItemManagerMock,
    );
  }

  public function testHandle(): void {
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle(
      ['request_data' => ['foo' => 'bar']]
    );

    $costItemsData = [
      new CostItemData([
        'type' => 'testType',
        'identifier' => 'test',
        'amount' => 1.23,
        'properties' => ['x' => 1.23, 'y' => 'z'],
        'dataPointer' => '/foo',
        'dataType' => 'object',
        'clearing' => NULL,
      ]),
    ];
    $costItems = [
      ApplicationCostItemFactory::createApplicationCostItem([
        'type' => 'testType',
        'identifier' => 'test',
        'amount' => 1.23,
        'properties' => ['x' => 1.23, 'y' => 'z'],
        'data_pointer' => '/foo',
      ]),
    ];

    $this->costItemManagerMock->expects(static::once())->method('updateAll')
      ->with($applicationProcessBundle->getApplicationProcess()->getId(), $costItems);

    $this->handler->handle(new ApplicationCostItemsPersistCommand(
      $applicationProcessBundle, $costItemsData,
    ));
  }

}
