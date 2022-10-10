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

namespace Civi\Funding\Form\Handler;

use Civi\Api4\RemoteFundingApplicationProcess;
use Civi\Api4\RemoteFundingCase;
use Civi\Funding\ApplicationProcess\ApplicationProcessManager;
use Civi\Funding\ApplicationProcess\ApplicationProcessStatusDeterminer;
use Civi\Funding\EntityFactory\ApplicationProcessFactory;
use Civi\Funding\EntityFactory\FundingCaseFactory;
use Civi\Funding\EntityFactory\FundingCaseTypeFactory;
use Civi\Funding\EntityFactory\FundingProgramFactory;
use Civi\Funding\Event\Remote\ApplicationProcess\SubmitApplicationFormEvent;
use Civi\Funding\Event\Remote\FundingCase\SubmitNewApplicationFormEvent;
use Civi\Funding\Form\ApplicationFormFactoryInterface;
use Civi\Funding\Form\Validation\FormValidatorInterface;
use Civi\Funding\Form\Validation\ValidationResult;
use Civi\Funding\FundingCase\FundingCaseManager;
use Civi\Funding\Mock\Form\ApplicationFormMock;
use Civi\Funding\Mock\Form\ValidatedApplicationDataMock;
use Opis\JsonSchema\Errors\ValidationError;
use Opis\JsonSchema\Info\DataInfo;
use Opis\JsonSchema\Info\SchemaInfo;
use Opis\JsonSchema\Schemas\EmptySchema;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Systopia\JsonSchema\Errors\ErrorCollector;

/**
 * @covers \Civi\Funding\Form\Handler\SubmitApplicationFormHandler
 */
final class SubmitApplicationFormHandlerTest extends TestCase {

  /**
   * @var \Civi\Funding\ApplicationProcess\ApplicationProcessManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $applicationProcessManagerMock;

  /**
   * @var \Civi\Funding\Form\ApplicationFormFactoryInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $formFactoryMock;

  /**
   * @var \Civi\Funding\FundingCase\FundingCaseManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $fundingCaseManagerMock;

  /**
   * @var \Civi\Funding\Form\Handler\SubmitApplicationFormHandler
   */
  private SubmitApplicationFormHandler $handler;

  /**
   * @var \Civi\Funding\ApplicationProcess\ApplicationProcessStatusDeterminer&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $statusDeterminerMock;

  /**
   * @var \Civi\Funding\Form\Validation\FormValidatorInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $validatorMock;

  protected function setUp(): void {
    parent::setUp();
    $this->applicationProcessManagerMock = $this->createMock(ApplicationProcessManager::class);
    $this->formFactoryMock = $this->createMock(ApplicationFormFactoryInterface::class);
    $this->fundingCaseManagerMock = $this->createMock(FundingCaseManager::class);
    $this->statusDeterminerMock = $this->createMock(ApplicationProcessStatusDeterminer::class);
    $this->validatorMock = $this->createMock(FormValidatorInterface::class);
    $this->handler = new SubmitApplicationFormHandler(
      $this->applicationProcessManagerMock,
      $this->formFactoryMock,
      $this->fundingCaseManagerMock,
      $this->statusDeterminerMock,
      $this->validatorMock,
    );
  }

  public function testHandleSubmitFormValid(): void {
    $event = $this->createSubmitFormEvent();
    $validatedForm = new ApplicationFormMock();
    $this->formFactoryMock->expects(static::once())->method('createFormOnSubmit')
      ->with($event)
      ->willReturn($validatedForm);

    $validationResult = new ValidationResult([], new ErrorCollector());
    $this->validatorMock->expects(static::once())->method('validate')
      ->with($validatedForm)
      ->willReturn($validationResult);

    $validatedData = new ValidatedApplicationDataMock();
    $this->formFactoryMock->expects(static::once())->method('createValidatedData')->with(
      $event->getApplicationProcess(),
      $event->getFundingCaseType(),
      $validationResult
    )->willReturn($validatedData);

    $this->statusDeterminerMock->method('getStatus')
      ->with($event->getApplicationProcess()->getStatus(), ValidatedApplicationDataMock::ACTION)
      ->willReturn('new_status');

    $this->applicationProcessManagerMock->expects(static::once())->method('update')
      ->with($event->getContactId(), $event->getApplicationProcess());

    $expectedForm = new ApplicationFormMock();
    $this->formFactoryMock->expects(static::once())->method('createForm')->with(
      $event->getApplicationProcess(),
      $event->getFundingProgram(),
      $event->getFundingCase(),
      $event->getFundingCaseType(),
    )->willReturn($expectedForm);

    $this->handler->handleSubmitForm($event);

    $applicationProcess = $event->getApplicationProcess();
    static::assertSame(ValidatedApplicationDataMock::TITLE, $applicationProcess->getTitle());
    static::assertSame(ValidatedApplicationDataMock::SHORT_DESCRIPTION, $applicationProcess->getShortDescription());
    static::assertEquals(new \DateTime(ValidatedApplicationDataMock::START_DATE),
      $applicationProcess->getStartDate());
    static::assertEquals(new \DateTime(ValidatedApplicationDataMock::END_DATE),
      $applicationProcess->getEndDate());
    static::assertSame(ValidatedApplicationDataMock::AMOUNT_REQUESTED, $applicationProcess->getAmountRequested());
    static::assertSame(ValidatedApplicationDataMock::APPLICATION_DATA, $applicationProcess->getRequestData());
    static::assertSame('new_status', $applicationProcess->getStatus());

    static::assertSame(SubmitApplicationFormEvent::ACTION_SHOW_FORM, $event->getAction());
    static::assertSame($expectedForm, $event->getForm());
  }

  public function testHandleSubmitFormInvalid(): void {
    $event = $this->createSubmitFormEvent();
    $validatedForm = new ApplicationFormMock();
    $this->formFactoryMock->expects(static::once())->method('createFormOnSubmit')
      ->with($event)
      ->willReturn($validatedForm);

    $postValidationData = ['foo' => 'baz'];
    $errorCollector = new ErrorCollector();
    $errorCollector->addError(
      new ValidationError(
        'keyword',
        new EmptySchema(new SchemaInfo(FALSE, NULL)),
        new DataInfo('bar', 'string', NULL, ['foo']),
        'Invalid value'
      )
    );
    $this->validatorMock->expects(static::once())->method('validate')->with($validatedForm)->willReturn(
      new ValidationResult($postValidationData, $errorCollector)
    );

    $this->applicationProcessManagerMock->expects(static::never())->method('update');

    $this->handler->handleSubmitForm($event);

    static::assertSame(SubmitApplicationFormEvent::ACTION_SHOW_VALIDATION, $event->getAction());
    static::assertSame(['/foo' => ['Invalid value']], $event->getErrors());
  }

  public function testHandleSubmitNewForm(): void {
    $event = $this->createSubmitNewFormEvent();
    $validatedForm = new ApplicationFormMock();
    $this->formFactoryMock->expects(static::once())->method('createNewFormOnSubmit')
      ->with($event)
      ->willReturn($validatedForm);

    $validationResult = new ValidationResult([], new ErrorCollector());
    $this->validatorMock->expects(static::once())->method('validate')
      ->with($validatedForm)
      ->willReturn($validationResult);

    $validatedData = new ValidatedApplicationDataMock();
    $this->formFactoryMock->expects(static::once())->method('createNewValidatedData')->with(
      $event->getFundingCaseType(),
      $validationResult
    )->willReturn($validatedData);

    $this->statusDeterminerMock->expects(static::once())->method('getStatusForNew')
      ->with(ValidatedApplicationDataMock::ACTION)
      ->willReturn('test_status');

    $fundingCase = FundingCaseFactory::createFundingCase();
    $this->fundingCaseManagerMock->expects(static::once())->method('create')
      ->with($event->getContactId(), [
        'funding_program' => $event->getFundingProgram(),
        'funding_case_type' => $event->getFundingCaseType(),
        // TODO: This has to be adapted when fixed in the CUT.
        'recipient_contact_id' => $event->getContactId(),
      ])->willReturn($fundingCase);

    $applicationProcess = ApplicationProcessFactory::createApplicationProcess();
    $this->applicationProcessManagerMock->expects(static::once())->method('create')
      ->with($event->getContactId(), $fundingCase, 'test_status', $validatedData)
      ->willReturn($applicationProcess);

    $expectedForm = new ApplicationFormMock();
    $this->formFactoryMock->expects(static::once())->method('createForm')->with(
      $applicationProcess,
      $event->getFundingProgram(),
      $fundingCase,
      $event->getFundingCaseType(),
    )->willReturn($expectedForm);

    $this->handler->handleSubmitNewForm($event);

    static::assertSame(SubmitApplicationFormEvent::ACTION_SHOW_FORM, $event->getAction());
    static::assertSame($expectedForm, $event->getForm());
  }

  public function testHandleSubmitNewFormInvalid(): void {
    $event = $this->createSubmitNewFormEvent();
    $validatedForm = new ApplicationFormMock();
    $this->formFactoryMock->expects(static::once())->method('createNewFormOnSubmit')
      ->with($event)
      ->willReturn($validatedForm);

    $postValidationData = ['foo' => 'baz'];
    $errorCollector = new ErrorCollector();
    $errorCollector->addError(
      new ValidationError(
        'keyword',
        new EmptySchema(new SchemaInfo(FALSE, NULL)),
        new DataInfo('bar', 'string', NULL, ['foo']),
        'Invalid value'
      )
    );
    $this->validatorMock->expects(static::once())->method('validate')->with($validatedForm)->willReturn(
      new ValidationResult($postValidationData, $errorCollector)
    );

    $this->applicationProcessManagerMock->expects(static::never())->method('update');

    $this->handler->handleSubmitNewForm($event);

    static::assertSame(SubmitApplicationFormEvent::ACTION_SHOW_VALIDATION, $event->getAction());
    static::assertSame(['/foo' => ['Invalid value']], $event->getErrors());
  }

  public function testSupportsFundingCaseType(): void {
    $this->formFactoryMock->expects(static::once())->method('supportsFundingCaseType')
      ->with('test')
      ->willReturn(TRUE);

    static::assertTrue($this->handler->supportsFundingCaseType('test'));
  }

  private function createSubmitNewFormEvent(): SubmitNewApplicationFormEvent {
    return new SubmitNewApplicationFormEvent(RemoteFundingCase::_getEntityName(), 'SubmitNewApplicationForm', [
      'remoteContactId' => '00',
      'contactId' => 1,
      'fundingProgram' => FundingProgramFactory::createFundingProgram(),
      'fundingCaseType' => FundingCaseTypeFactory::createFundingCaseType(),
      'data' => ['test' => 'foo'],
    ]);
  }

  private function createSubmitFormEvent(): SubmitApplicationFormEvent {
    return new SubmitApplicationFormEvent(RemoteFundingApplicationProcess::_getEntityName(), 'SubmitForm', [
      'remoteContactId' => '00',
      'contactId' => 1,
      'applicationProcess' => ApplicationProcessFactory::createApplicationProcess(),
      'fundingCase' => FundingCaseFactory::createFundingCase(),
      'fundingProgram' => FundingProgramFactory::createFundingProgram(),
      'fundingCaseType' => FundingCaseTypeFactory::createFundingCaseType(),
      'data' => ['test' => 'foo'],
    ]);
  }

}
