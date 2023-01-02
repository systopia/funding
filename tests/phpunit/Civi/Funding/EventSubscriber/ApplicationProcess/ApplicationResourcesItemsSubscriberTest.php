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

namespace Civi\Funding\EventSubscriber\ApplicationProcess;

use Civi\Funding\ApplicationProcess\Command\ApplicationResourcesItemsAddIdentifiersCommand;
use Civi\Funding\ApplicationProcess\Command\ApplicationResourcesItemsPersistCommand;
use Civi\Funding\ApplicationProcess\Handler\ApplicationResourcesItemsAddIdentifiersHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationResourcesItemsPersistHandlerInterface;
use Civi\Funding\EntityFactory\ApplicationProcessFactory;
use Civi\Funding\EntityFactory\FundingCaseFactory;
use Civi\Funding\EntityFactory\FundingCaseTypeFactory;
use Civi\Funding\EntityFactory\FundingProgramFactory;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessCreatedEvent;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessPreCreateEvent;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessPreUpdateEvent;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessUpdatedEvent;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\EventSubscriber\ApplicationProcess\ApplicationResourcesItemsSubscriber
 */
final class ApplicationResourcesItemsSubscriberTest extends TestCase {

  /**
   * @var \Civi\Funding\ApplicationProcess\Handler\ApplicationResourcesItemsAddIdentifiersHandlerInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $resourcesItemsAddIdentifiersHandlerMock;

  /**
   * @var \Civi\Funding\ApplicationProcess\Handler\ApplicationResourcesItemsPersistHandlerInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $resourcesItemsPersistHandlerMock;

  private ApplicationResourcesItemsSubscriber $subscriber;

  protected function setUp(): void {
    parent::setUp();
    $this->resourcesItemsAddIdentifiersHandlerMock = $this->createMock(
      ApplicationResourcesItemsAddIdentifiersHandlerInterface::class
    );
    $this->resourcesItemsPersistHandlerMock = $this->createMock(
      ApplicationResourcesItemsPersistHandlerInterface::class
    );
    $this->subscriber = new ApplicationResourcesItemsSubscriber(
      $this->resourcesItemsAddIdentifiersHandlerMock,
      $this->resourcesItemsPersistHandlerMock
    );
  }

  public function testGetSubscribedEvents(): void {
    $expectedSubscriptions = [
      ApplicationProcessPreCreateEvent::class => 'onPreCreate',
      ApplicationProcessCreatedEvent::class => 'onCreated',
      ApplicationProcessPreUpdateEvent::class => 'onPreUpdate',
      ApplicationProcessUpdatedEvent::class => 'onUpdated',
    ];

    static::assertEquals($expectedSubscriptions, $this->subscriber::getSubscribedEvents());

    foreach ($expectedSubscriptions as $method) {
      static::assertTrue(method_exists(get_class($this->subscriber), $method));
    }
  }

  public function testOnPreCreate(): void {
    $applicationProcess = ApplicationProcessFactory::createApplicationProcess();
    $fundingCase = FundingCaseFactory::createFundingCase();
    $fundingCaseType = FundingCaseTypeFactory::createFundingCaseType();
    $fundingProgram = FundingProgramFactory::createFundingProgram();
    $event = new ApplicationProcessPreCreateEvent(
      2,
      $applicationProcess,
      $fundingCase,
      $fundingCaseType,
      $fundingProgram,
    );

    $this->resourcesItemsAddIdentifiersHandlerMock->expects(static::once())->method('handle')
      ->with(new ApplicationResourcesItemsAddIdentifiersCommand($applicationProcess, $fundingCase, $fundingCaseType))
      ->willReturn(['bar' => 'baz']);
    $this->subscriber->onPreCreate($event);
    static::assertSame(['bar' => 'baz'], $applicationProcess->getRequestData());
  }

  public function testOnCreated(): void {
    $applicationProcess = ApplicationProcessFactory::createApplicationProcess();
    $fundingCase = FundingCaseFactory::createFundingCase();
    $fundingCaseType = FundingCaseTypeFactory::createFundingCaseType();
    $fundingProgram = FundingProgramFactory::createFundingProgram();
    $event = new ApplicationProcessCreatedEvent(
      2,
      $applicationProcess,
      $fundingCase,
      $fundingCaseType,
      $fundingProgram,
    );

    $this->resourcesItemsPersistHandlerMock->expects(static::once())->method('handle')
      ->with(new ApplicationResourcesItemsPersistCommand(
        $applicationProcess,
        $fundingCase,
        $fundingCaseType,
        NULL,
      )
    );
    $this->subscriber->onCreated($event);
  }

  public function testOnPreUpdate(): void {
    $previousApplicationProcess = ApplicationProcessFactory::createApplicationProcess(['title' => 'Previous']);
    $applicationProcess = ApplicationProcessFactory::createApplicationProcess();
    $fundingCase = FundingCaseFactory::createFundingCase();
    $fundingCaseType = FundingCaseTypeFactory::createFundingCaseType();
    $event = new ApplicationProcessPreUpdateEvent(
      2,
      $previousApplicationProcess,
      $applicationProcess,
      $fundingCase,
      $fundingCaseType,
    );

    $this->resourcesItemsAddIdentifiersHandlerMock->expects(static::once())->method('handle')
      ->with(new ApplicationResourcesItemsAddIdentifiersCommand($applicationProcess, $fundingCase, $fundingCaseType))
      ->willReturn(['bar' => 'baz']);
    $this->subscriber->onPreUpdate($event);
    static::assertSame(['bar' => 'baz'], $applicationProcess->getRequestData());
  }

  public function testOnUpdated(): void {
    $previousApplicationProcess = ApplicationProcessFactory::createApplicationProcess(['title' => 'Previous']);
    $applicationProcess = ApplicationProcessFactory::createApplicationProcess();
    $fundingCase = FundingCaseFactory::createFundingCase();
    $fundingCaseType = FundingCaseTypeFactory::createFundingCaseType();
    $event = new ApplicationProcessUpdatedEvent(
      2,
      $previousApplicationProcess,
      $applicationProcess,
      $fundingCase,
      $fundingCaseType,
    );

    $this->resourcesItemsPersistHandlerMock->expects(static::once())->method('handle')
      ->with(new ApplicationResourcesItemsPersistCommand(
        $applicationProcess,
        $fundingCase,
        $fundingCaseType,
        $previousApplicationProcess,
      )
    );
    $this->subscriber->onUpdated($event);
  }

}
