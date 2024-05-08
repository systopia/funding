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
use Civi\Funding\ApplicationProcess\Command\ApplicationResourcesItemsPersistCommand;
use Civi\Funding\ApplicationProcess\JsonSchema\ResourcesItem\ResourcesItemData;
use Civi\Funding\EntityFactory\ApplicationProcessBundleFactory;
use Civi\Funding\EntityFactory\ApplicationResourcesItemFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\ApplicationProcess\Handler\ApplicationResourcesItemsPersistHandler
 */
final class ApplicationResourcesItemsPersistHandlerTest extends TestCase {

  /**
   * @var \Civi\Funding\ApplicationProcess\ApplicationResourcesItemManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $resourcesItemManagerMock;

  private ApplicationResourcesItemsPersistHandler $handler;

  protected function setUp(): void {
    parent::setUp();
    $this->resourcesItemManagerMock = $this->createMock(ApplicationResourcesItemManager::class);
    $this->handler = new ApplicationResourcesItemsPersistHandler(
      $this->resourcesItemManagerMock,
    );
  }

  public function testHandle(): void {
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle(
      ['request_data' => ['foo' => 'bar']]
    );

    $resourcesItemsData = [
      new ResourcesItemData([
        'type' => 'testType',
        'identifier' => 'test',
        'amount' => 1.23,
        'properties' => ['x' => 1.23, 'y' => 'z'],
        'dataPointer' => '/foo',
        'dataType' => 'object',
        'clearing' => NULL,
      ]),
    ];
    $resourcesItems = [
      ApplicationResourcesItemFactory::createApplicationResourcesItem([
        'id' => NULL,
        'type' => 'testType',
        'identifier' => 'test',
        'amount' => 1.23,
        'properties' => ['x' => 1.23, 'y' => 'z'],
        'data_pointer' => '/foo',
      ]),
    ];
    $this->resourcesItemManagerMock->expects(static::once())->method('updateAll')
      ->with($applicationProcessBundle->getApplicationProcess()->getId(), $resourcesItems);

    $this->handler->handle(new ApplicationResourcesItemsPersistCommand(
      $applicationProcessBundle, $resourcesItemsData
    ));
  }

}
