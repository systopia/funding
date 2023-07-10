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

use Civi\Funding\ApplicationProcess\Command\ApplicationFilesAddIdentifiersCommand;
use Civi\Funding\ApplicationProcess\Command\ApplicationFilesPersistCommand;
use Civi\Funding\ApplicationProcess\Command\ApplicationFormNewSubmitResult;
use Civi\Funding\ApplicationProcess\Command\ApplicationFormSubmitResult;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFilesAddIdentifiersHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFilesPersistHandlerInterface;
use Civi\Funding\EntityFactory\ApplicationProcessBundleFactory;
use Civi\Funding\EntityFactory\ApplicationProcessFactory;
use Civi\Funding\EntityFactory\ExternalFileFactory;
use Civi\Funding\Event\ApplicationProcess\ApplicationFormSubmitSuccessEvent;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessCreatedEvent;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessPreCreateEvent;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessPreUpdateEvent;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessUpdatedEvent;
use Civi\Funding\Form\Validation\ValidationResult;
use Civi\Funding\Mock\Form\FundingCaseType\TestValidatedData;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Systopia\JsonSchema\Errors\ErrorCollector;

/**
 * @covers \Civi\Funding\EventSubscriber\ApplicationProcess\ApplicationFilesSubscriber
 */
final class ApplicationFilesSubscriberTest extends TestCase {

  /**
   * @var \Civi\Funding\ApplicationProcess\Handler\ApplicationFilesAddIdentifiersHandlerInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $filesAddIdentifiersHandlerMock;

  /**
   * @var \Civi\Funding\ApplicationProcess\Handler\ApplicationFilesPersistHandlerInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $filesPersistHandlerMock;

  private ApplicationFilesSubscriber $subscriber;

  protected function setUp(): void {
    parent::setUp();
    $this->filesAddIdentifiersHandlerMock = $this->createMock(ApplicationFilesAddIdentifiersHandlerInterface::class);
    $this->filesPersistHandlerMock = $this->createMock(ApplicationFilesPersistHandlerInterface::class);
    $this->subscriber = new ApplicationFilesSubscriber(
      $this->filesAddIdentifiersHandlerMock,
      $this->filesPersistHandlerMock,
    );
  }

  public function testGetSubscribedEvents(): void {
    $expectedSubscriptions = [
      ApplicationProcessPreCreateEvent::class => 'onPreCreate',
      ApplicationProcessCreatedEvent::class => 'onCreated',
      ApplicationProcessPreUpdateEvent::class => 'onPreUpdate',
      ApplicationProcessUpdatedEvent::class => 'onUpdated',
      ApplicationFormSubmitSuccessEvent::class => 'onSubmitSuccess',
    ];

    static::assertEquals($expectedSubscriptions, $this->subscriber::getSubscribedEvents());

    foreach ($expectedSubscriptions as $method) {
      static::assertTrue(method_exists(get_class($this->subscriber), $method));
    }
  }

  public function testOnCreated(): void {
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle();
    $externalFile = ExternalFileFactory::create();
    $this->filesPersistHandlerMock->expects(static::once())->method('handle')
      ->with(new ApplicationFilesPersistCommand($applicationProcessBundle, NULL))
      ->willReturn(['https://example.org' => $externalFile]);

    $this->subscriber->onCreated(new ApplicationProcessCreatedEvent(2, $applicationProcessBundle));

    $submitResult = ApplicationFormNewSubmitResult::createSuccess(
      new ValidationResult([], new ErrorCollector()),
      new TestValidatedData([]),
      $applicationProcessBundle,
    );
    $formSuccessEvent = new ApplicationFormSubmitSuccessEvent(2, $applicationProcessBundle, [], $submitResult);
    $this->subscriber->onSubmitSuccess($formSuccessEvent);
    static::assertSame(['https://example.org' => $externalFile], $formSuccessEvent->getResult()->getFiles());
  }

  public function testOnPreCreate(): void {
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle();
    $this->filesAddIdentifiersHandlerMock->expects(static::once())->method('handle')
      ->with(new ApplicationFilesAddIdentifiersCommand($applicationProcessBundle));

    $this->subscriber->onPreCreate(new ApplicationProcessPreCreateEvent(2, $applicationProcessBundle));
  }

  public function testOnPreUpdate(): void {
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle();
    $previousApplicationProcess = ApplicationProcessFactory::createApplicationProcess();
    $this->filesAddIdentifiersHandlerMock->expects(static::once())->method('handle')
      ->with(new ApplicationFilesAddIdentifiersCommand($applicationProcessBundle));

    $this->subscriber->onPreUpdate(new ApplicationProcessPreUpdateEvent(
      2,
      $previousApplicationProcess,
      $applicationProcessBundle,
    ));
  }

  public function testOnUpdated(): void {
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle();
    $previousApplicationProcess = ApplicationProcessFactory::createApplicationProcess();
    $externalFile = ExternalFileFactory::create();
    $this->filesPersistHandlerMock->expects(static::once())->method('handle')
      ->with(new ApplicationFilesPersistCommand($applicationProcessBundle, $previousApplicationProcess))
      ->willReturn(['https://example.org' => $externalFile]);

    $this->subscriber->onUpdated(new ApplicationProcessUpdatedEvent(
      2,
      $previousApplicationProcess,
      $applicationProcessBundle,
    ));

    $submitResult = ApplicationFormSubmitResult::createSuccess(
      new ValidationResult([], new ErrorCollector()),
      new TestValidatedData([]),
    );
    $formSuccessEvent = new ApplicationFormSubmitSuccessEvent(2, $applicationProcessBundle, [], $submitResult);
    $this->subscriber->onSubmitSuccess($formSuccessEvent);
    static::assertSame(['https://example.org' => $externalFile], $formSuccessEvent->getResult()->getFiles());
  }

}
