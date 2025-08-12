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

use Civi\Funding\ApplicationProcess\Command\ApplicationCostItemsPersistCommand;
use Civi\Funding\ApplicationProcess\Command\ApplicationFormSubmitResult;
use Civi\Funding\ApplicationProcess\Handler\ApplicationCostItemsPersistHandlerInterface;
use Civi\Funding\ApplicationProcess\JsonSchema\CostItem\CostItemData;
use Civi\Funding\EntityFactory\ApplicationProcessBundleFactory;
use Civi\Funding\EntityFactory\ApplicationSnapshotFactory;
use Civi\Funding\Event\ApplicationProcess\ApplicationFormSubmitSuccessEvent;
use Civi\Funding\Mock\ApplicationProcess\Form\Validation\ApplicationFormValidationResultFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\EventSubscriber\ApplicationProcess\ApplicationCostItemsSubscriber
 */
final class ApplicationCostItemsSubscriberTest extends TestCase {

  /**
   * @var \Civi\Funding\ApplicationProcess\Handler\ApplicationCostItemsPersistHandlerInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $costItemsPersistHandlerMock;

  private ApplicationCostItemsSubscriber $subscriber;

  protected function setUp(): void {
    parent::setUp();

    $this->costItemsPersistHandlerMock = $this->createMock(ApplicationCostItemsPersistHandlerInterface::class);
    $this->subscriber = new ApplicationCostItemsSubscriber(
      $this->costItemsPersistHandlerMock,
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
    $costItemsData = ['test' => $this->createCostItem()];
    $validationResult = ApplicationFormValidationResultFactory::createValid([], [], $costItemsData);
    $event = new ApplicationFormSubmitSuccessEvent(
      $applicationProcessBundle,
      $applicationProcessBundle->getApplicationProcess()->getRequestData(),
      ApplicationFormSubmitResult::createSuccess($validationResult),
    );

    $this->costItemsPersistHandlerMock->expects(static::once())->method('handle')
      ->with(new ApplicationCostItemsPersistCommand($applicationProcessBundle, $costItemsData));
    $this->subscriber->onFormSubmitSuccess($event);
  }

  public function testOnFormSubmitSuccessReadOnly(): void {
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle();
    $costItemsData = ['test' => $this->createCostItem()];
    $validationResult = ApplicationFormValidationResultFactory::createValid([], [], $costItemsData, [], TRUE);
    $event = new ApplicationFormSubmitSuccessEvent(
      $applicationProcessBundle,
      $applicationProcessBundle->getApplicationProcess()->getRequestData(),
      ApplicationFormSubmitResult::createSuccess($validationResult),
    );

    $this->costItemsPersistHandlerMock->expects(static::never())->method('handle');
    $this->subscriber->onFormSubmitSuccess($event);
  }

  public function testOnFormSubmitSuccessWithRestore(): void {
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle();
    $applicationProcessBundle->getApplicationProcess()->setRestoredSnapshot(
      ApplicationSnapshotFactory::createApplicationSnapshot()
    );
    $costItemsData = ['test' => $this->createCostItem()];
    $validationResult = ApplicationFormValidationResultFactory::createValid([], [], $costItemsData);
    $event = new ApplicationFormSubmitSuccessEvent(
      $applicationProcessBundle,
      $applicationProcessBundle->getApplicationProcess()->getRequestData(),
      ApplicationFormSubmitResult::createSuccess($validationResult),
    );

    $this->costItemsPersistHandlerMock->expects(static::never())->method('handle');
    $this->subscriber->onFormSubmitSuccess($event);
  }

  private function createCostItem(): CostItemData {
    return new CostItemData([
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
