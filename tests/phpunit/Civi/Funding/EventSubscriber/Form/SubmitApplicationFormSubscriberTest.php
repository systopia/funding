<?php
/*
 * Copyright (C) 2022 SYSTOPIA GmbH
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

namespace Civi\Funding\EventSubscriber\Form;

use Civi\Api4\RemoteFundingApplicationProcess;
use Civi\Api4\RemoteFundingCase;
use Civi\Funding\ApplicationProcess\ApplicationProcessBundleLoader;
use Civi\Funding\ApplicationProcess\Command\ApplicationFormNewSubmitCommand;
use Civi\Funding\ApplicationProcess\Command\ApplicationFormNewSubmitResult;
use Civi\Funding\ApplicationProcess\Command\ApplicationFormSubmitCommand;
use Civi\Funding\ApplicationProcess\Command\ApplicationFormSubmitResult;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormNewSubmitHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormSubmitHandlerInterface;
use Civi\Funding\Entity\FullApplicationProcessStatus;
use Civi\Funding\EntityFactory\ApplicationProcessBundleFactory;
use Civi\Funding\EntityFactory\ExternalFileFactory;
use Civi\Funding\EntityFactory\FundingCaseTypeFactory;
use Civi\Funding\EntityFactory\FundingProgramFactory;
use Civi\Funding\Event\Remote\ApplicationProcess\SubmitApplicationFormEvent;
use Civi\Funding\Event\Remote\FundingCase\SubmitNewApplicationFormEvent;
use Civi\Funding\Form\Application\ApplicationValidationResult;
use Civi\Funding\Form\RemoteSubmitResponseActions;
use Civi\Funding\Mock\Form\ValidatedApplicationDataMock;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\EventSubscriber\Form\SubmitApplicationFormSubscriber
 * @covers \Civi\Funding\Event\Remote\ApplicationProcess\SubmitApplicationFormEvent
 * @covers \Civi\Funding\Event\Remote\FundingCase\SubmitNewApplicationFormEvent
 * @covers \Civi\Funding\Event\Remote\AbstractFundingSubmitFormEvent
 */
final class SubmitApplicationFormSubscriberTest extends TestCase {

  /**
   * @var \Civi\Funding\ApplicationProcess\ApplicationProcessBundleLoader&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $applicationProcessBundleLoaderMock;

  /**
   * @var \Civi\Funding\ApplicationProcess\Handler\ApplicationFormNewSubmitHandlerInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $newSubmitHandlerMock;

  /**
   * @var \Civi\Funding\ApplicationProcess\Handler\ApplicationFormSubmitHandlerInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $submitHandlerMock;

  private SubmitApplicationFormSubscriber $subscriber;

  /**
   * @phpstan-var array<int, FullApplicationProcessStatus>
   */
  private array $statusList;

  protected function setUp(): void {
    parent::setUp();
    $this->applicationProcessBundleLoaderMock = $this->createMock(ApplicationProcessBundleLoader::class);
    $this->newSubmitHandlerMock = $this->createMock(ApplicationFormNewSubmitHandlerInterface::class);
    $this->submitHandlerMock = $this->createMock(ApplicationFormSubmitHandlerInterface::class);
    $this->subscriber = new SubmitApplicationFormSubscriber(
      $this->applicationProcessBundleLoaderMock,
      $this->newSubmitHandlerMock,
      $this->submitHandlerMock,
    );

    $this->statusList = [23 => new FullApplicationProcessStatus('status', NULL, NULL)];
  }

  public function testSubmitSubscribedEvents(): void {
    $expectedSubscriptions = [
      SubmitApplicationFormEvent::getEventName() => 'onSubmitForm',
      SubmitNewApplicationFormEvent::getEventName() => 'onSubmitNewForm',
    ];

    static::assertEquals($expectedSubscriptions, SubmitApplicationFormSubscriber::getSubscribedEvents());

    foreach ($expectedSubscriptions as $method) {
      static::assertTrue(method_exists(SubmitApplicationFormSubscriber::class, $method));
    }
  }

  public function testOnSubmitForm(): void {
    $event = $this->createSubmitFormEvent();
    $command = new ApplicationFormSubmitCommand(
      $event->getContactId(), $event->getApplicationProcessBundle(), $this->statusList, $event->getData(),
    );

    $postValidationData = ['foo' => 'bar'];
    $validatedData = new ValidatedApplicationDataMock($postValidationData, ['action' => 'save']);
    $validationResult = ApplicationValidationResult::newValid($validatedData, FALSE);
    $result = ApplicationFormSubmitResult::createSuccess($validationResult);
    $result->setFiles([
      'https://example.org/test.txt' => ExternalFileFactory::create(
        ['uri' => 'https://example.net/test.txt'],
      ),
    ]);
    $this->submitHandlerMock->expects(static::once())->method('handle')
      ->with($command)
      ->willReturn($result);

    $this->subscriber->onSubmitForm($event);

    static::assertSame(RemoteSubmitResponseActions::CLOSE_FORM, $event->getAction());
    static::assertSame('Saved', $event->getMessage());
    static::assertSame(['https://example.org/test.txt' => 'https://example.net/test.txt'], $event->getFiles());
  }

  public function testOnSubmitFormInvalid(): void {
    $event = $this->createSubmitFormEvent();
    $command = new ApplicationFormSubmitCommand(
      $event->getContactId(), $event->getApplicationProcessBundle(), $this->statusList, $event->getData(),
    );

    $postValidationData = ['foo' => 'baz'];
    $validatedData = new ValidatedApplicationDataMock($postValidationData, ['action' => 'save']);
    $errorMessages = ['/a/b' => ['error']];
    $validationResult = ApplicationValidationResult::newInvalid($errorMessages, $validatedData);
    $result = ApplicationFormSubmitResult::createError($validationResult);
    $this->submitHandlerMock->expects(static::once())->method('handle')
      ->with($command)
      ->willReturn($result);

    $this->subscriber->onSubmitForm($event);

    static::assertSame(RemoteSubmitResponseActions::SHOW_VALIDATION, $event->getAction());
    static::assertSame($errorMessages, $event->getErrors());
    static::assertSame('Validation failed', $event->getMessage());
  }

  public function testOnSubmitNewForm(): void {
    $event = $this->createSubmitNewFormEvent();
    $command = new ApplicationFormNewSubmitCommand(
      $event->getContactId(),
      $event->getFundingCaseType(),
      $event->getFundingProgram(),
      $event->getData()
    );

    $postValidationData = ['foo' => 'bar'];
    $validatedData = new ValidatedApplicationDataMock($postValidationData, ['action' => 'save']);
    $validationResult = ApplicationValidationResult::newValid($validatedData, FALSE);
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle();
    $result = ApplicationFormNewSubmitResult::createSuccess(
      $validationResult,
      $applicationProcessBundle,
    );
    $result->setFiles([
      'https://example.org/test.txt' => ExternalFileFactory::create(
        ['uri' => 'https://example.net/test.txt'],
      ),
    ]);
    $this->newSubmitHandlerMock->expects(static::once())->method('handle')
      ->with($command)
      ->willReturn($result);

    $this->subscriber->onSubmitNewForm($event);

    static::assertSame(RemoteSubmitResponseActions::CLOSE_FORM, $event->getAction());
    static::assertSame('Saved', $event->getMessage());
    static::assertSame(['https://example.org/test.txt' => 'https://example.net/test.txt'], $event->getFiles());
  }

  public function testOnSubmitNewFormInvalid(): void {
    $event = $this->createSubmitNewFormEvent();
    $command = new ApplicationFormNewSubmitCommand(
      $event->getContactId(),
      $event->getFundingCaseType(),
      $event->getFundingProgram(),
      $event->getData()
    );

    $postValidationData = ['foo' => 'bar'];
    $validatedData = new ValidatedApplicationDataMock($postValidationData);
    $errorMessages = ['/a/b' => ['error']];
    $validationResult = ApplicationValidationResult::newInvalid($errorMessages, $validatedData);
    $result = ApplicationFormNewSubmitResult::createError($validationResult);
    $this->newSubmitHandlerMock->expects(static::once())->method('handle')
      ->with($command)
      ->willReturn($result);

    $this->subscriber->onSubmitNewForm($event);

    static::assertSame(RemoteSubmitResponseActions::SHOW_VALIDATION, $event->getAction());
    static::assertSame($errorMessages, $event->getErrors());
    static::assertSame('Validation failed', $event->getMessage());
  }

  private function createSubmitNewFormEvent(): SubmitNewApplicationFormEvent {
    return new SubmitNewApplicationFormEvent(RemoteFundingCase::_getEntityName(), 'SubmitNewApplicationForm', [
      'remoteContactId' => '00',
      'contactId' => 1,
      'fundingProgram' => FundingProgramFactory::createFundingProgram(),
      'fundingCaseType' => FundingCaseTypeFactory::createFundingCaseType(),
      'data' => [],
    ]);
  }

  private function createSubmitFormEvent(): SubmitApplicationFormEvent {
    $event = new SubmitApplicationFormEvent(RemoteFundingApplicationProcess::_getEntityName(), 'submitForm', [
      'remoteContactId' => '00',
      'contactId' => 1,
      'applicationProcessBundle' => ApplicationProcessBundleFactory::createApplicationProcessBundle(),
      'data' => [],
    ]);

    $this->applicationProcessBundleLoaderMock->method('getStatusList')
      ->with($event->getApplicationProcessBundle())
      ->willReturn($this->statusList);

    return $event;
  }

}
