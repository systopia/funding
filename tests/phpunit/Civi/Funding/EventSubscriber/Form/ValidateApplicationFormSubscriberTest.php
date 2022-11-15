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
use Civi\Funding\ApplicationProcess\Command\ApplicationFormNewValidateCommand;
use Civi\Funding\ApplicationProcess\Command\ApplicationFormValidateCommand;
use Civi\Funding\ApplicationProcess\Command\ApplicationFormValidateResult;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormNewValidateHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormValidateHandlerInterface;
use Civi\Funding\EntityFactory\ApplicationProcessFactory;
use Civi\Funding\EntityFactory\FundingCaseFactory;
use Civi\Funding\EntityFactory\FundingCaseTypeFactory;
use Civi\Funding\EntityFactory\FundingProgramFactory;
use Civi\Funding\Event\Remote\ApplicationProcess\ValidateApplicationFormEvent;
use Civi\Funding\Event\Remote\FundingCase\ValidateNewApplicationFormEvent;
use Civi\Funding\Form\Validation\ValidationResult;
use Civi\Funding\Form\ValidationErrorFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Systopia\JsonSchema\Errors\ErrorCollector;

/**
 * @covers \Civi\Funding\EventSubscriber\Form\ValidateApplicationFormSubscriber
 */
final class ValidateApplicationFormSubscriberTest extends TestCase {

  private ValidateApplicationFormSubscriber $subscriber;

  /**
   * @var \Civi\Funding\ApplicationProcess\Handler\ApplicationFormNewValidateHandlerInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $newValidateHandlerMock;

  /**
   * @var \Civi\Funding\ApplicationProcess\Handler\ApplicationFormValidateHandlerInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $validateHandlerMock;

  protected function setUp(): void {
    parent::setUp();
    $this->newValidateHandlerMock = $this->createMock(ApplicationFormNewValidateHandlerInterface::class);
    $this->validateHandlerMock = $this->createMock(ApplicationFormValidateHandlerInterface::class);
    $this->subscriber = new ValidateApplicationFormSubscriber(
      $this->newValidateHandlerMock,
      $this->validateHandlerMock
    );
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
      $event->getApplicationProcess(),
      $event->getFundingProgram(),
      $event->getFundingCase(),
      $event->getFundingCaseType(),
      $event->getData()
    );

    $validationResult = new ValidationResult([], new ErrorCollector());
    $result = ApplicationFormValidateResult::create($validationResult);
    $this->validateHandlerMock->expects(static::once())->method('handle')
      ->with($command)
      ->willReturn($result);

    $this->subscriber->onValidateForm($event);

    static::assertTrue($event->isValid());
    static::assertSame([], $event->getErrors());
  }

  public function testOnValidateFormInvalid(): void {
    $event = $this->createValidateFormEvent();
    $command = new ApplicationFormValidateCommand(
      $event->getApplicationProcess(),
      $event->getFundingProgram(),
      $event->getFundingCase(),
      $event->getFundingCaseType(),
      $event->getData()
    );

    $errorCollector = new ErrorCollector();
    $errorCollector->addError(ValidationErrorFactory::createValidationError());
    $validationResult = new ValidationResult([], $errorCollector);
    $result = ApplicationFormValidateResult::create($validationResult);
    $this->validateHandlerMock->expects(static::once())->method('handle')
      ->with($command)
      ->willReturn($result);

    $this->subscriber->onValidateForm($event);

    static::assertFalse($event->isValid());
    static::assertSame(['/foo' => ['Invalid value']], $event->getErrors());
  }

  public function testOnValidateNewForm(): void {
    $event = $this->createValidateNewFormEvent();
    $command = new ApplicationFormNewValidateCommand(
      $event->getContactId(),
      $event->getFundingProgram(),
      $event->getFundingCaseType(),
      $event->getData()
    );
    $validationResult = new ValidationResult([], new ErrorCollector());
    $result = ApplicationFormValidateResult::create($validationResult);
    $this->newValidateHandlerMock->expects(static::once())->method('handle')
      ->with($command)
      ->willReturn($result);

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

    $errorCollector = new ErrorCollector();
    $errorCollector->addError(ValidationErrorFactory::createValidationError());
    $validationResult = new ValidationResult([], $errorCollector);
    $result = ApplicationFormValidateResult::create($validationResult);
    $this->newValidateHandlerMock->expects(static::once())->method('handle')
      ->with($command)
      ->willReturn($result);

    $this->subscriber->onValidateNewForm($event);

    static::assertFalse($event->isValid());
    static::assertSame(['/foo' => ['Invalid value']], $event->getErrors());
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
    return new ValidateApplicationFormEvent(RemoteFundingApplicationProcess::_getEntityName(), 'ValidateForm', [
      'remoteContactId' => '00',
      'contactId' => 1,
      'applicationProcess' => ApplicationProcessFactory::createApplicationProcess(),
      'fundingCase' => FundingCaseFactory::createFundingCase(),
      'fundingProgram' => FundingProgramFactory::createFundingProgram(),
      'fundingCaseType' => FundingCaseTypeFactory::createFundingCaseType(),
      'data' => [],
    ]);
  }

}
