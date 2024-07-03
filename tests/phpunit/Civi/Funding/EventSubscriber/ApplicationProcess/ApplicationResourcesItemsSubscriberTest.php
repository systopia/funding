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

use Civi\Funding\ApplicationProcess\Command\ApplicationFormSubmitResult;
use Civi\Funding\ApplicationProcess\Command\ApplicationResourcesItemsPersistCommand;
use Civi\Funding\ApplicationProcess\Handler\ApplicationResourcesItemsPersistHandlerInterface;
use Civi\Funding\ApplicationProcess\JsonSchema\ResourcesItem\ResourcesItemData;
use Civi\Funding\EntityFactory\ApplicationProcessBundleFactory;
use Civi\Funding\EntityFactory\ApplicationSnapshotFactory;
use Civi\Funding\Event\ApplicationProcess\ApplicationFormSubmitSuccessEvent;
use Civi\Funding\Form\Application\ApplicationValidationResult;
use Civi\Funding\Mock\Form\ValidatedApplicationDataMock;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\EventSubscriber\ApplicationProcess\ApplicationResourcesItemsSubscriber
 */
final class ApplicationResourcesItemsSubscriberTest extends TestCase {

  /**
   * @var \Civi\Funding\ApplicationProcess\Handler\ApplicationResourcesItemsPersistHandlerInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $resourcesItemsPersistHandlerMock;

  private ApplicationResourcesItemsSubscriber $subscriber;

  protected function setUp(): void {
    parent::setUp();
    $this->resourcesItemsPersistHandlerMock = $this->createMock(
      ApplicationResourcesItemsPersistHandlerInterface::class
    );
    $this->subscriber = new ApplicationResourcesItemsSubscriber(
      $this->resourcesItemsPersistHandlerMock
    );
  }

  public function testGetSubscribedEvents(): void {
    $expectedSubscriptions = [
      ApplicationFormSubmitSuccessEvent::class => 'onFormSubmitSuccess',
    ];

    static::assertEquals($expectedSubscriptions, $this->subscriber::getSubscribedEvents());

    foreach ($expectedSubscriptions as $method) {
      static::assertTrue(method_exists(get_class($this->subscriber), $method));
    }
  }

  public function testOnFormSubmitSuccess(): void {
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle();
    $resourcesItemsData = ['test' => $this->createResourcesItem()];
    $validationResult = ApplicationValidationResult::newValid(
      new ValidatedApplicationDataMock([], ['resourcesItemsData' => $resourcesItemsData]),
      FALSE
    );
    $event = new ApplicationFormSubmitSuccessEvent(
      2,
      $applicationProcessBundle,
      $applicationProcessBundle->getApplicationProcess()->getRequestData(),
      ApplicationFormSubmitResult::createSuccess($validationResult),
    );

    $this->resourcesItemsPersistHandlerMock->expects(static::once())->method('handle')
      ->with(new ApplicationResourcesItemsPersistCommand($applicationProcessBundle, $resourcesItemsData));
    $this->subscriber->onFormSubmitSuccess($event);
  }

  public function testOnFormSubmitSuccessReadOnly(): void {
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle();
    $resourcesItemsData = ['test' => $this->createResourcesItem()];
    $validationResult = ApplicationValidationResult::newValid(
      new ValidatedApplicationDataMock([], ['resourcesItemsData' => $resourcesItemsData]),
      TRUE
    );
    $event = new ApplicationFormSubmitSuccessEvent(
      2,
      $applicationProcessBundle,
      $applicationProcessBundle->getApplicationProcess()->getRequestData(),
      ApplicationFormSubmitResult::createSuccess($validationResult),
    );

    $this->resourcesItemsPersistHandlerMock->expects(static::never())->method('handle');
    $this->subscriber->onFormSubmitSuccess($event);
  }

  public function testOnFormSubmitSuccessWithRestore(): void {
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle();
    $applicationProcessBundle->getApplicationProcess()->setRestoredSnapshot(
      ApplicationSnapshotFactory::createApplicationSnapshot()
    );
    $resourcesItemsData = ['test' => $this->createResourcesItem()];
    $validationResult = ApplicationValidationResult::newValid(
      new ValidatedApplicationDataMock([], ['resourcesItemsData' => $resourcesItemsData]),
      FALSE
    );
    $event = new ApplicationFormSubmitSuccessEvent(
      2,
      $applicationProcessBundle,
      $applicationProcessBundle->getApplicationProcess()->getRequestData(),
      ApplicationFormSubmitResult::createSuccess($validationResult),
    );

    $this->resourcesItemsPersistHandlerMock->expects(static::never())->method('handle');
    $this->subscriber->onFormSubmitSuccess($event);
  }

  private function createResourcesItem(): ResourcesItemData {
    return new ResourcesItemData([
      'type' => 'testType',
      'identifier' => 'test',
      'amount' => 1.23,
      'properties' => [],
      'dataPointer' => '/foo',
      'dataType' => 'number',
      'clearing' => NULL,
    ]);
  }

}
