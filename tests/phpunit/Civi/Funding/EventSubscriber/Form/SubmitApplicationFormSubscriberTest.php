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
use Civi\Funding\Event\Remote\ApplicationProcess\SubmitApplicationFormEvent;
use Civi\Funding\Event\Remote\FundingCase\SubmitNewApplicationFormEvent;
use Civi\Funding\Form\Handler\SubmitApplicationFormHandlerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\EventSubscriber\Form\SubmitApplicationFormSubscriber
 */
final class SubmitApplicationFormSubscriberTest extends TestCase {

  /**
   * @var \Civi\Funding\Form\Handler\SubmitApplicationFormHandlerInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $formHandlerMock;

  private SubmitApplicationFormSubscriber $subscriber;

  protected function setUp(): void {
    parent::setUp();
    $this->formHandlerMock = $this->createMock(SubmitApplicationFormHandlerInterface::class);
    $this->subscriber = new SubmitApplicationFormSubscriber($this->formHandlerMock);
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

    $this->formHandlerMock->expects(static::once())->method('supportsFundingCaseType')
      ->with($event->getFundingCaseType()->getName())
      ->willReturn(TRUE);

    $this->formHandlerMock->expects(static::once())->method('handleSubmitForm')
      ->with($event);

    $this->subscriber->onSubmitForm($event);
  }

  public function testOnSubmitFormNotSupported(): void {
    $event = $this->createSubmitFormEvent();

    $this->formHandlerMock->expects(static::once())->method('supportsFundingCaseType')
      ->with($event->getFundingCaseType()->getName())
      ->willReturn(FALSE);

    $this->formHandlerMock->expects(static::never())->method('handleSubmitForm');

    $this->subscriber->onSubmitForm($event);
  }

  public function testOnSubmitNewForm(): void {
    $event = $this->createSubmitNewFormEvent();

    $this->formHandlerMock->expects(static::once())->method('supportsFundingCaseType')
      ->with($event->getFundingCaseType()->getName())
      ->willReturn(TRUE);

    $this->formHandlerMock->expects(static::once())->method('handleSubmitNewForm')
      ->with($event);

    $this->subscriber->onSubmitNewForm($event);
  }

  public function testOnSubmitNewFormNotSupported(): void {
    $event = $this->createSubmitNewFormEvent();

    $this->formHandlerMock->expects(static::once())->method('supportsFundingCaseType')
      ->with($event->getFundingCaseType()->getName())
      ->willReturn(FALSE);

    $this->formHandlerMock->expects(static::never())->method('handleSubmitNewForm');

    $this->subscriber->onSubmitNewForm($event);
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
