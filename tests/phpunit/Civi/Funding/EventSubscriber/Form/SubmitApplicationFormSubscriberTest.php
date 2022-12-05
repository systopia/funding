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
use Civi\Funding\ApplicationProcess\Command\ApplicationFormCreateCommand;
use Civi\Funding\ApplicationProcess\Command\ApplicationFormNewSubmitCommand;
use Civi\Funding\ApplicationProcess\Command\ApplicationFormNewSubmitResult;
use Civi\Funding\ApplicationProcess\Command\ApplicationFormSubmitCommand;
use Civi\Funding\ApplicationProcess\Command\ApplicationFormSubmitResult;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormCreateHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormNewSubmitHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormSubmitHandlerInterface;
use Civi\Funding\EntityFactory\ApplicationProcessFactory;
use Civi\Funding\EntityFactory\FundingCaseFactory;
use Civi\Funding\EntityFactory\FundingCaseTypeFactory;
use Civi\Funding\EntityFactory\FundingProgramFactory;
use Civi\Funding\Event\Remote\ApplicationProcess\SubmitApplicationFormEvent;
use Civi\Funding\Event\Remote\FundingCase\SubmitNewApplicationFormEvent;
use Civi\Funding\Form\Validation\ValidationResult;
use Civi\Funding\Form\ValidationErrorFactory;
use Civi\Funding\Mock\Form\ApplicationFormMock;
use Civi\Funding\Mock\Form\ValidatedApplicationDataMock;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Systopia\JsonSchema\Errors\ErrorCollector;

/**
 * @covers \Civi\Funding\EventSubscriber\Form\SubmitApplicationFormSubscriber
 */
final class SubmitApplicationFormSubscriberTest extends TestCase {

  /**
   * @var \Civi\Funding\ApplicationProcess\Handler\ApplicationFormCreateHandlerInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $formCreateHandlerMock;

  /**
   * @var \Civi\Funding\ApplicationProcess\Handler\ApplicationFormNewSubmitHandlerInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $newSubmitHandlerMock;

  /**
   * @var \Civi\Funding\ApplicationProcess\Handler\ApplicationFormSubmitHandlerInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $submitHandlerMock;

  private SubmitApplicationFormSubscriber $subscriber;

  protected function setUp(): void {
    parent::setUp();
    $this->formCreateHandlerMock = $this->createMock(ApplicationFormCreateHandlerInterface::class);
    $this->newSubmitHandlerMock = $this->createMock(ApplicationFormNewSubmitHandlerInterface::class);
    $this->submitHandlerMock = $this->createMock(ApplicationFormSubmitHandlerInterface::class);
    $this->subscriber = new SubmitApplicationFormSubscriber(
      $this->formCreateHandlerMock,
      $this->newSubmitHandlerMock,
      $this->submitHandlerMock,
    );
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
      $event->getContactId(),
      $event->getApplicationProcess(),
      $event->getFundingCase(),
      $event->getFundingCaseType(),
      $event->getFundingProgram(),
      $event->getData()
    );

    $postValidationData = ['foo' => 'bar'];
    $validationResult = new ValidationResult($postValidationData, new ErrorCollector());
    $validatedData = new ValidatedApplicationDataMock($postValidationData, ['action' => 'save']);
    $result = ApplicationFormSubmitResult::createSuccess($validationResult, $validatedData);
    $this->submitHandlerMock->expects(static::once())->method('handle')
      ->with($command)
      ->willReturn($result);

    $form = new ApplicationFormMock();
    $this->formCreateHandlerMock->expects(static::once())->method('handle')->with(new ApplicationFormCreateCommand(
      $event->getApplicationProcess(),
      $event->getFundingCase(),
      $event->getFundingCaseType(),
      $event->getFundingProgram(),
      $postValidationData
    ))->willReturn($form);

    $this->subscriber->onSubmitForm($event);

    static::assertSame(SubmitApplicationFormEvent::ACTION_SHOW_FORM, $event->getAction());
    static::assertSame($form, $event->getForm());
    static::assertSame('Saved', $event->getMessage());
  }

  public function testOnSubmitFormInvalid(): void {
    $event = $this->createSubmitFormEvent();
    $command = new ApplicationFormSubmitCommand(
      $event->getContactId(),
      $event->getApplicationProcess(),
      $event->getFundingCase(),
      $event->getFundingCaseType(),
      $event->getFundingProgram(),
      $event->getData()
    );

    $postValidationData = ['foo' => 'baz'];
    $errorCollector = new ErrorCollector();
    $errorCollector->addError(ValidationErrorFactory::createValidationError());
    $validationResult = new ValidationResult($postValidationData, $errorCollector);
    $result = ApplicationFormSubmitResult::createError($validationResult);
    $this->submitHandlerMock->expects(static::once())->method('handle')
      ->with($command)
      ->willReturn($result);

    $this->subscriber->onSubmitForm($event);

    static::assertSame(SubmitApplicationFormEvent::ACTION_SHOW_VALIDATION, $event->getAction());
    static::assertSame(['/foo' => ['Invalid value']], $event->getErrors());
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
    $validationResult = new ValidationResult($postValidationData, new ErrorCollector());
    $validatedData = new ValidatedApplicationDataMock($postValidationData, ['action' => 'save']);
    $applicationProcess = ApplicationProcessFactory::createApplicationProcess();
    $fundingCase = FundingCaseFactory::createFundingCase();
    $result = ApplicationFormNewSubmitResult::createSuccess(
      $validationResult,
      $validatedData,
      $applicationProcess,
      $fundingCase
    );
    $this->newSubmitHandlerMock->expects(static::once())->method('handle')
      ->with($command)
      ->willReturn($result);

    $form = new ApplicationFormMock();
    $this->formCreateHandlerMock->expects(static::once())->method('handle')->with(new ApplicationFormCreateCommand(
      $applicationProcess,
      $fundingCase,
      $event->getFundingCaseType(),
      $event->getFundingProgram(),
      $postValidationData
    ))->willReturn($form);

    $this->subscriber->onSubmitNewForm($event);

    static::assertSame(SubmitApplicationFormEvent::ACTION_SHOW_FORM, $event->getAction());
    static::assertSame($form, $event->getForm());
    static::assertSame('Saved', $event->getMessage());
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
    $errorCollector = new ErrorCollector();
    $errorCollector->addError(ValidationErrorFactory::createValidationError());
    $validationResult = new ValidationResult($postValidationData, $errorCollector);
    $result = ApplicationFormNewSubmitResult::createError($validationResult);
    $this->newSubmitHandlerMock->expects(static::once())->method('handle')
      ->with($command)
      ->willReturn($result);

    $this->subscriber->onSubmitNewForm($event);

    static::assertSame(SubmitApplicationFormEvent::ACTION_SHOW_VALIDATION, $event->getAction());
    static::assertSame(['/foo' => ['Invalid value']], $event->getErrors());
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
    return new SubmitApplicationFormEvent(RemoteFundingApplicationProcess::_getEntityName(), 'submitForm', [
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
