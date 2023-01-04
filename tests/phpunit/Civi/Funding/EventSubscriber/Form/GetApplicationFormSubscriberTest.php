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
use Civi\Funding\ApplicationProcess\Command\ApplicationFormNewCreateCommand;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormCreateHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormNewCreateHandlerInterface;
use Civi\Funding\EntityFactory\ApplicationProcessBundleFactory;
use Civi\Funding\EntityFactory\FundingCaseTypeFactory;
use Civi\Funding\EntityFactory\FundingProgramFactory;
use Civi\Funding\Event\Remote\ApplicationProcess\GetApplicationFormEvent;
use Civi\Funding\Event\Remote\FundingCase\GetNewApplicationFormEvent;
use Civi\Funding\Mock\Form\ApplicationFormMock;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\EventSubscriber\Form\GetApplicationFormSubscriber
 */
final class GetApplicationFormSubscriberTest extends TestCase {

  private GetApplicationFormSubscriber $subscriber;

  /**
   * @var \Civi\Funding\ApplicationProcess\Handler\ApplicationFormCreateHandlerInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $createHandlerMock;

  /**
   * @var \Civi\Funding\ApplicationProcess\Handler\ApplicationFormNewCreateHandlerInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $newCreateHandlerMock;

  protected function setUp(): void {
    parent::setUp();
    $this->createHandlerMock = $this->createMock(ApplicationFormCreateHandlerInterface::class);
    $this->newCreateHandlerMock = $this->createMock(ApplicationFormNewCreateHandlerInterface::class);
    $this->subscriber = new GetApplicationFormSubscriber(
      $this->createHandlerMock,
      $this->newCreateHandlerMock
    );
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
    $command = new ApplicationFormCreateCommand($event->getApplicationProcessBundle());

    $form = new ApplicationFormMock();
    $this->createHandlerMock->expects(static::once())->method('handle')
      ->with($command)
      ->willReturn($form);

    $this->subscriber->onGetForm($event);
    static::assertSame($form->getJsonSchema(), $event->getJsonSchema());
    static::assertSame($form->getUiSchema(), $event->getUiSchema());
    static::assertSame($form->getData(), $event->getData());
  }

  public function testOnGetNewForm(): void {
    $event = $this->createGetNewFormEvent();

    $command = new ApplicationFormNewCreateCommand(
      $event->getContactId(),
      $event->getFundingCaseType(),
      $event->getFundingProgram()
    );

    $form = new ApplicationFormMock();
    $this->newCreateHandlerMock->expects(static::once())->method('handle')
      ->with($command)
      ->willReturn($form);

    $this->subscriber->onGetNewForm($event);
    static::assertSame($form->getJsonSchema(), $event->getJsonSchema());
    static::assertSame($form->getUiSchema(), $event->getUiSchema());
    static::assertSame($form->getData(), $event->getData());
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
      'applicationProcessBundle' => ApplicationProcessBundleFactory::createApplicationProcessBundle(),
    ]);
  }

}
