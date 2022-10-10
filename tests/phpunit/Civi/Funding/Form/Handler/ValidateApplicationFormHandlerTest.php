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
use Civi\Funding\EntityFactory\ApplicationProcessFactory;
use Civi\Funding\EntityFactory\FundingCaseFactory;
use Civi\Funding\EntityFactory\FundingCaseTypeFactory;
use Civi\Funding\EntityFactory\FundingProgramFactory;
use Civi\Funding\Event\Remote\ApplicationProcess\ValidateApplicationFormEvent;
use Civi\Funding\Event\Remote\FundingCase\ValidateNewApplicationFormEvent;
use Civi\Funding\Form\ApplicationFormFactoryInterface;
use Civi\Funding\Form\Validation\FormValidatorInterface;
use Civi\Funding\Form\Validation\ValidationResult;
use Civi\Funding\Mock\Form\ApplicationFormMock;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Systopia\JsonSchema\Errors\ErrorCollector;

/**
 * @covers \Civi\Funding\Form\Handler\ValidateApplicationFormHandler
 */
final class ValidateApplicationFormHandlerTest extends TestCase {

  /**
   * @var \Civi\Funding\Form\ApplicationFormFactoryInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $formFactoryMock;

  /**
   * @var \Civi\Funding\Form\Handler\ValidateApplicationFormHandler
   */
  private ValidateApplicationFormHandler $handler;

  /**
   * @var \Civi\Funding\Form\Validation\FormValidatorInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $validatorMock;

  protected function setUp(): void {
    parent::setUp();
    $this->formFactoryMock = $this->createMock(ApplicationFormFactoryInterface::class);
    $this->validatorMock = $this->createMock(FormValidatorInterface::class);
    $this->handler = new ValidateApplicationFormHandler(
      $this->formFactoryMock,
      $this->validatorMock,
    );
  }

  public function testHandleValidateForm(): void {
    $event = $this->createValidateFormEvent();
    $form = new ApplicationFormMock();
    $this->formFactoryMock->expects(static::once())->method('createFormOnValidate')
      ->with($event)
      ->willReturn($form);

    $postValidationData = ['foo' => 'baz'];
    $this->validatorMock->expects(static::once())->method('validate')->with($form)->willReturn(
      new ValidationResult($postValidationData, new ErrorCollector())
    );

    $this->handler->handleValidateForm($event);

    static::assertTrue($event->isValid());
    static::assertSame([], $event->getErrors());
  }

  public function testHandleValidateNewForm(): void {
    $event = $this->createValidateNewFormEvent();
    $form = new ApplicationFormMock();
    $this->formFactoryMock->expects(static::once())->method('createNewFormOnValidate')
      ->with($event)
      ->willReturn($form);

    $postValidationData = ['foo' => 'baz'];
    $this->validatorMock->expects(static::once())->method('validate')->with($form)->willReturn(
      new ValidationResult($postValidationData, new ErrorCollector())
    );

    $this->handler->handleValidateNewForm($event);

    static::assertTrue($event->isValid());
    static::assertSame([], $event->getErrors());
  }

  public function testSupportsFundingCaseType(): void {
    $this->formFactoryMock->expects(static::once())->method('supportsFundingCaseType')
      ->with('test')
      ->willReturn(TRUE);

    static::assertTrue($this->handler->supportsFundingCaseType('test'));
  }

  private function createValidateNewFormEvent(): ValidateNewApplicationFormEvent {
    return new ValidateNewApplicationFormEvent(RemoteFundingCase::_getEntityName(), 'ValidateNewApplicationForm', [
      'remoteContactId' => '00',
      'contactId' => 1,
      'fundingProgram' => FundingProgramFactory::createFundingProgram(),
      'fundingCaseType' => FundingCaseTypeFactory::createFundingCaseType(),
      'data' => ['test' => 'foo'],
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
      'data' => ['test' => 'foo'],
    ]);
  }

}
