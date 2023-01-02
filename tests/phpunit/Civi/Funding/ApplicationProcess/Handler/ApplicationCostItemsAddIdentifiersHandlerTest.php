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

use Civi\Funding\ApplicationProcess\ApplicationCostItemsFactoryInterface;
use Civi\Funding\ApplicationProcess\Command\ApplicationCostItemsAddIdentifiersCommand;
use Civi\Funding\EntityFactory\ApplicationProcessFactory;
use Civi\Funding\EntityFactory\FundingCaseFactory;
use Civi\Funding\EntityFactory\FundingCaseTypeFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\ApplicationProcess\Handler\ApplicationCostItemsAddIdentifiersHandler
 * @covers \Civi\Funding\ApplicationProcess\Command\ApplicationCostItemsAddIdentifiersCommand
 */
final class ApplicationCostItemsAddIdentifiersHandlerTest extends TestCase {

  /**
   * @var \Civi\Funding\ApplicationProcess\ApplicationCostItemsFactoryInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $costItemsFactoryMock;

  private ApplicationCostItemsAddIdentifiersHandler $handler;

  protected function setUp(): void {
    parent::setUp();
    $this->costItemsFactoryMock = $this->createMock(ApplicationCostItemsFactoryInterface::class);
    $this->handler = new ApplicationCostItemsAddIdentifiersHandler($this->costItemsFactoryMock);
  }

  public function testHandle(): void {
    $applicationProcess = ApplicationProcessFactory::createApplicationProcess(['request_data' => ['foo' => 'bar']]);
    $fundingCase = FundingCaseFactory::createFundingCase();
    $fundingCaseType = FundingCaseTypeFactory::createFundingCaseType();

    $command = new ApplicationCostItemsAddIdentifiersCommand($applicationProcess, $fundingCase, $fundingCaseType);
    $this->costItemsFactoryMock->expects(static::once())->method('addIdentifiers')->with(['foo' => 'bar'])
      ->willReturn(['foo' => 'baz']);
    static::assertSame(['foo' => 'baz'], $this->handler->handle($command));
  }

}
