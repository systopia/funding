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
use Civi\Funding\ApplicationProcess\Command\ApplicationFormNewValidateCommand;
use Civi\Funding\ApplicationProcess\Command\ApplicationFormValidateCommand;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormNewValidateHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormValidateHandlerInterface;
use Civi\Funding\Entity\FullApplicationProcessStatus;
use Civi\Funding\EntityFactory\ApplicationProcessBundleFactory;
use Civi\Funding\EntityFactory\FundingCaseTypeFactory;
use Civi\Funding\EntityFactory\FundingProgramFactory;
use Civi\Funding\Event\Remote\ApplicationProcess\ValidateApplicationFormEvent;
use Civi\Funding\Event\Remote\FundingCase\ValidateNewApplicationFormEvent;
use Civi\Funding\Form\ApplicationValidationResult;
use Civi\Funding\Mock\Form\FundingCaseType\TestValidatedData;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\EventSubscriber\Form\ValidateApplicationFormSubscriber
 */
final class ValidateApplicationFormSubscriberTest extends TestCase {

  /**
   * @var \Civi\Funding\ApplicationProcess\ApplicationProcessBundleLoader&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $applicationProcessBundleLoaderMock;

  private ValidateApplicationFormSubscriber $subscriber;

  /**
   * @var \Civi\Funding\ApplicationProcess\Handler\ApplicationFormNewValidateHandlerInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $newValidateHandlerMock;

  /**
   * @var \Civi\Funding\ApplicationProcess\Handler\ApplicationFormValidateHandlerInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $validateHandlerMock;

  /**
   * @phpstan-var array<int, FullApplicationProcessStatus>
   */
  private array $statusList;

  protected function setUp(): void {
    parent::setUp();
    $this->applicationProcessBundleLoaderMock = $this->createMock(ApplicationProcessBundleLoader::class);
    $this->newValidateHandlerMock = $this->createMock(ApplicationFormNewValidateHandlerInterface::class);
    $this->validateHandlerMock = $this->createMock(ApplicationFormValidateHandlerInterface::class);
    $this->subscriber = new ValidateApplicationFormSubscriber(
      $this->applicationProcessBundleLoaderMock,
      $this->newValidateHandlerMock,
      $this->validateHandlerMock
    );

    $this->statusList = [23 => new FullApplicationProcessStatus('status', NULL, NULL)];
  }

  public function testValidateSubscribedEvents(): void {
    $expectedSubscriptions = [
      ValidateApplicationFormEvent::getEventName() => 'onValidateForm',
      ValidateNewApplicationFormEvent::getEventName() => 'onValidateNewForm',
    ];

    static::assertEquals($expectedSubscriptions, ValidateApplicationFormSubscriber::getSubscribedEvents());

    foreach ($expectedSubscriptions as $method) {
      static::assertTrue(method_exists(ValidateApplicationFormSubscriber::class, $method));
    }
  }

  public function testOnValidateForm(): void {
    $event = $this->createValidateFormEvent();
    $command = new ApplicationFormValidateCommand(
      $event->getApplicationProcessBundle(),
      $this->statusList,
      $event->getData(),
      20,
    );

    $validationResult = ApplicationValidationResult::newValid(new TestValidatedData([]), FALSE);
    $this->validateHandlerMock->expects(static::once())->method('handle')
      ->with($command)
      ->willReturn($validationResult);

    $this->subscriber->onValidateForm($event);

    static::assertTrue($event->isValid());
    static::assertSame([], $event->getErrors());
  }

  public function testOnValidateFormInvalid(): void {
    $event = $this->createValidateFormEvent();
    $command = new ApplicationFormValidateCommand(
      $event->getApplicationProcessBundle(),
      $this->statusList,
      $event->getData(),
      20,
    );

    $errorMessages = ['/a/b' => ['error']];
    $validationResult = ApplicationValidationResult::newInvalid(
      $errorMessages,
      new TestValidatedData([])
    );
    $this->validateHandlerMock->expects(static::once())->method('handle')
      ->with($command)
      ->willReturn($validationResult);

    $this->subscriber->onValidateForm($event);

    static::assertFalse($event->isValid());
    static::assertSame($errorMessages, $event->getErrors());
  }

  public function testOnValidateNewForm(): void {
    $event = $this->createValidateNewFormEvent();
    $command = new ApplicationFormNewValidateCommand(
      $event->getContactId(),
      $event->getFundingProgram(),
      $event->getFundingCaseType(),
      $event->getData()
    );
    $validationResult = ApplicationValidationResult::newValid(new TestValidatedData([]), FALSE);
    $this->newValidateHandlerMock->expects(static::once())->method('handle')
      ->with($command)
      ->willReturn($validationResult);

    $this->subscriber->onValidateNewForm($event);

    static::assertTrue($event->isValid());
    static::assertSame([], $event->getErrors());
  }

  public function testOnValidateNewFormInvalid(): void {
    $event = $this->createValidateNewFormEvent();
    $command = new ApplicationFormNewValidateCommand(
      $event->getContactId(),
      $event->getFundingProgram(),
      $event->getFundingCaseType(),
      $event->getData()
    );

    $errorMessages = ['/a/b' => ['error']];
    $validationResult = ApplicationValidationResult::newInvalid(
      $errorMessages,
      new TestValidatedData([])
    );
    $this->newValidateHandlerMock->expects(static::once())->method('handle')
      ->with($command)
      ->willReturn($validationResult);

    $this->subscriber->onValidateNewForm($event);

    static::assertFalse($event->isValid());
    static::assertSame($errorMessages, $event->getErrors());
  }

  private function createValidateNewFormEvent(): ValidateNewApplicationFormEvent {
    return new ValidateNewApplicationFormEvent(RemoteFundingCase::_getEntityName(), 'ValidateNewApplicationForm', [
      'remoteContactId' => '00',
      'contactId' => 1,
      'fundingProgram' => FundingProgramFactory::createFundingProgram(),
      'fundingCaseType' => FundingCaseTypeFactory::createFundingCaseType(),
      'data' => [],
    ]);
  }

  private function createValidateFormEvent(): ValidateApplicationFormEvent {
    $event = new ValidateApplicationFormEvent(RemoteFundingApplicationProcess::_getEntityName(), 'ValidateForm', [
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
