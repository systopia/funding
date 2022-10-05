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
use Civi\Funding\Event\Remote\ApplicationProcess\GetApplicationFormEvent;
use Civi\Funding\Event\Remote\FundingCase\GetNewApplicationFormEvent;
use Civi\Funding\Form\ApplicationFormFactoryInterface;
use Civi\Funding\Mock\Form\ApplicationFormMock;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\Form\Handler\GetApplicationFormHandler
 */
final class GetApplicationFormHandlerTest extends TestCase {

  /**
   * @var \Civi\Funding\Form\ApplicationFormFactoryInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $formFactoryMock;

  /**
   * @var \Civi\Funding\Form\Handler\GetApplicationFormHandler
   */
  private GetApplicationFormHandler $handler;

  protected function setUp(): void {
    parent::setUp();
    $this->formFactoryMock = $this->createMock(ApplicationFormFactoryInterface::class);
    $this->handler = new GetApplicationFormHandler($this->formFactoryMock);
  }

  public function testHandleGetForm(): void {
    $event = $this->createGetFormEvent();

    $form = new ApplicationFormMock();
    $this->formFactoryMock->expects(static::once())->method('createFormOnGet')
      ->with($event)
      ->willReturn($form);

    $this->handler->handleGetForm($event);

    static::assertSame($form->getJsonSchema(), $event->getJsonSchema());
    static::assertSame($form->getUiSchema(), $event->getUiSchema());
    static::assertSame($form->getData(), $event->getData());
  }

  public function testHandleGetNewForm(): void {
    $event = $this->createGetNewFormEvent();

    $form = new ApplicationFormMock();
    $this->formFactoryMock->expects(static::once())->method('createNewFormOnGet')
      ->with($event)
      ->willReturn($form);

    $this->handler->handleGetNewForm($event);

    static::assertSame($form->getJsonSchema(), $event->getJsonSchema());
    static::assertSame($form->getUiSchema(), $event->getUiSchema());
    static::assertSame($form->getData(), $event->getData());
  }

  public function testSupportsFundingCaseType(): void {
    $this->formFactoryMock->expects(static::once())->method('supportsFundingCaseType')
      ->with('test')
      ->willReturn(TRUE);

    static::assertTrue($this->handler->supportsFundingCaseType('test'));
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
