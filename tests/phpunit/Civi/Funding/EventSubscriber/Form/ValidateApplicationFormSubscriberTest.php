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
use Civi\Funding\EntityFactory\ApplicationProcessFactory;
use Civi\Funding\EntityFactory\FundingCaseFactory;
use Civi\Funding\EntityFactory\FundingCaseTypeFactory;
use Civi\Funding\EntityFactory\FundingProgramFactory;
use Civi\Funding\Event\Remote\ApplicationProcess\ValidateApplicationFormEvent;
use Civi\Funding\Event\Remote\FundingCase\ValidateNewApplicationFormEvent;
use Civi\Funding\Form\Handler\ValidateApplicationFormHandlerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\EventSubscriber\Form\ValidateApplicationFormSubscriber
 */
final class ValidateApplicationFormSubscriberTest extends TestCase {

  /**
   * @var \Civi\Funding\Form\Handler\ValidateApplicationFormHandlerInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $formHandlerMock;

  private ValidateApplicationFormSubscriber $subscriber;

  protected function setUp(): void {
    parent::setUp();
    $this->formHandlerMock = $this->createMock(ValidateApplicationFormHandlerInterface::class);
    $this->subscriber = new ValidateApplicationFormSubscriber($this->formHandlerMock);
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

    $this->formHandlerMock->expects(static::once())->method('supportsFundingCaseType')
      ->with($event->getFundingCaseType()->getName())
      ->willReturn(TRUE);

    $this->formHandlerMock->expects(static::once())->method('handleValidateForm')
      ->with($event);

    $this->subscriber->onValidateForm($event);
  }

  public function testOnValidateFormNotSupported(): void {
    $event = $this->createValidateFormEvent();

    $this->formHandlerMock->expects(static::once())->method('supportsFundingCaseType')
      ->with($event->getFundingCaseType()->getName())
      ->willReturn(FALSE);

    $this->formHandlerMock->expects(static::never())->method('handleValidateForm');

    $this->subscriber->onValidateForm($event);
  }

  public function testOnValidateNewForm(): void {
    $event = $this->createValidateNewFormEvent();

    $this->formHandlerMock->expects(static::once())->method('supportsFundingCaseType')
      ->with($event->getFundingCaseType()->getName())
      ->willReturn(TRUE);

    $this->formHandlerMock->expects(static::once())->method('handleValidateNewForm')
      ->with($event);

    $this->subscriber->onValidateNewForm($event);
  }

  public function testOnValidateNewFormNotSupported(): void {
    $event = $this->createValidateNewFormEvent();

    $this->formHandlerMock->expects(static::once())->method('supportsFundingCaseType')
      ->with($event->getFundingCaseType()->getName())
      ->willReturn(FALSE);

    $this->formHandlerMock->expects(static::never())->method('handleValidateNewForm');

    $this->subscriber->onValidateNewForm($event);
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
