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

use Civi\Funding\ApplicationProcess\ApplicationResourcesItemsFactoryInterface;
use Civi\Funding\ApplicationProcess\Command\ApplicationResourcesItemsAddIdentifiersCommand;
use Civi\Funding\EntityFactory\ApplicationProcessBundleFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\ApplicationProcess\Handler\ApplicationResourcesItemsAddIdentifiersHandler
 * @covers \Civi\Funding\ApplicationProcess\Command\ApplicationResourcesItemsAddIdentifiersCommand
 */
final class ApplicationResourcesItemsAddIdentifiersHandlerTest extends TestCase {

  /**
   * @var \Civi\Funding\ApplicationProcess\ApplicationResourcesItemsFactoryInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $resourcesItemsFactoryMock;

  private ApplicationResourcesItemsAddIdentifiersHandler $handler;

  protected function setUp(): void {
    parent::setUp();
    $this->resourcesItemsFactoryMock = $this->createMock(ApplicationResourcesItemsFactoryInterface::class);
    $this->handler = new ApplicationResourcesItemsAddIdentifiersHandler($this->resourcesItemsFactoryMock);
  }

  public function testHandle(): void {
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle(
      ['request_data' => ['foo' => 'bar']]
    );

    $command = new ApplicationResourcesItemsAddIdentifiersCommand($applicationProcessBundle);
    $this->resourcesItemsFactoryMock->expects(static::once())->method('addIdentifiers')->with(['foo' => 'bar'])
      ->willReturn(['foo' => 'baz']);
    static::assertSame(['foo' => 'baz'], $this->handler->handle($command));
  }

}
