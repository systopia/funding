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
use Civi\Funding\Event\Remote\ApplicationProcess\GetApplicationFormEvent;
use Civi\Funding\Event\Remote\FundingCase\GetNewApplicationFormEvent;
use Civi\Funding\Form\Handler\GetApplicationFormHandlerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\EventSubscriber\Form\GetApplicationFormSubscriber
 */
final class GetApplicationFormSubscriberTest extends TestCase {

  /**
   * @var \Civi\Funding\Form\Handler\GetApplicationFormHandlerInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $formHandlerMock;

  private GetApplicationFormSubscriber $subscriber;

  protected function setUp(): void {
    parent::setUp();
    $this->formHandlerMock = $this->createMock(GetApplicationFormHandlerInterface::class);
    $this->subscriber = new GetApplicationFormSubscriber($this->formHandlerMock);
  }

  public function testGetSubscribedEvents(): void {
    $expectedSubscriptions = [
      GetApplicationFormEvent::getEventName() => 'onGetForm',
      GetNewApplicationFormEvent::getEventName() => 'onGetNewForm',
    ];

    static::assertEquals($expectedSubscriptions, GetApplicationFormSubscriber::getSubscribedEvents());

    foreach ($expectedSubscriptions as $method) {
      static::assertTrue(method_exists(GetApplicationFormSubscriber::class, $method));
    }
  }

  public function testOnGetForm(): void {
    $event = $this->createGetFormEvent();

    $this->formHandlerMock->expects(static::once())->method('supportsFundingCaseType')
      ->with($event->getFundingCaseType()->getName())
      ->willReturn(TRUE);

    $this->formHandlerMock->expects(static::once())->method('handleGetForm')
      ->with($event);

    $this->subscriber->onGetForm($event);
  }

  public function testOnGetFormNotSupported(): void {
    $event = $this->createGetFormEvent();

    $this->formHandlerMock->expects(static::once())->method('supportsFundingCaseType')
      ->with($event->getFundingCaseType()->getName())
      ->willReturn(FALSE);

    $this->formHandlerMock->expects(static::never())->method('handleGetForm');

    $this->subscriber->onGetForm($event);
  }

  public function testOnGetNewForm(): void {
    $event = $this->createGetNewFormEvent();

    $this->formHandlerMock->expects(static::once())->method('supportsFundingCaseType')
      ->with($event->getFundingCaseType()->getName())
      ->willReturn(TRUE);

    $this->formHandlerMock->expects(static::once())->method('handleGetNewForm')
      ->with($event);

    $this->subscriber->onGetNewForm($event);
  }

  public function testOnGetNewFormNotSupported(): void {
    $event = $this->createGetNewFormEvent();

    $this->formHandlerMock->expects(static::once())->method('supportsFundingCaseType')
      ->with($event->getFundingCaseType()->getName())
      ->willReturn(FALSE);

    $this->formHandlerMock->expects(static::never())->method('handleGetNewForm');

    $this->subscriber->onGetNewForm($event);
  }

  private function createGetNewFormEvent(): GetNewApplicationFormEvent {
    return new GetNewApplicationFormEvent(RemoteFundingCase::_getEntityName(), 'GetNewApplicationForm', [
      'remoteContactId' => '00',
      'contactId' => 1,
      'fundingProgram' => FundingProgramFactory::createFundingProgram(),
      'fundingCaseType' => FundingCaseTypeFactory::createFundingCaseType(),
    ]);
  }

  private function createGetFormEvent(): GetApplicationFormEvent {
    return new GetApplicationFormEvent(RemoteFundingApplicationProcess::_getEntityName(), 'GetForm', [
      'remoteContactId' => '00',
      'contactId' => 1,
      'applicationProcess' => ApplicationProcessFactory::createApplicationProcess(),
      'fundingCase' => FundingCaseFactory::createFundingCase(),
      'fundingProgram' => FundingProgramFactory::createFundingProgram(),
      'fundingCaseType' => FundingCaseTypeFactory::createFundingCaseType(),
    ]);
  }

}
