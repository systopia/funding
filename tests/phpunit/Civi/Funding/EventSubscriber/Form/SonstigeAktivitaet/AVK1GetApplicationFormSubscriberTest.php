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

namespace Civi\Funding\EventSubscriber\Form\SonstigeAktivitaet;

use Civi\Api4\RemoteFundingApplicationProcess;
use Civi\Funding\Event\Remote\ApplicationProcess\GetFormEvent;

/**
 * @covers \Civi\Funding\EventSubscriber\Form\SonstigeAktivitaet\AVK1GetApplicationFormSubscriber
 */
final class AVK1GetApplicationFormSubscriberTest extends AbstractApplicationFormSubscriberTest {

  public function testGetSubscribedEvents(): void {
    $expectedSubscriptions = [
      GetFormEvent::getEventName() => 'onGetForm',
    ];

    static::assertEquals($expectedSubscriptions, AVK1GetApplicationFormSubscriber::getSubscribedEvents());

    foreach ($expectedSubscriptions as $method) {
      static::assertTrue(method_exists(AVK1GetApplicationFormSubscriber::class, $method));
    }
  }

  public function testOnGetForm(): void {
    $subscriber = new AVK1GetApplicationFormSubscriber();
    $event = $this->createEvent();
    $subscriber->onGetForm($event);

    static::assertNotNull($event->getJsonSchema());
    static::assertNotNull($event->getUiSchema());
    static::assertEquals([
      'applicationProcessId' => 2,
      'foo' => 'bar',
    ], $event->getData());
  }

  public function testOnGetFormFundingCaseTypeNotMatch(): void {
    $subscriber = new AVK1GetApplicationFormSubscriber();
    $event = $this->createEvent('Foo');
    $subscriber->onGetForm($event);

    static::assertSame([], $event->getData());
    static::assertNull($event->getJsonSchema());
    static::assertNull($event->getUiSchema());
  }

  private function createEvent(string $fundingCaseTypeName = 'AVK1SonstigeAktivitaet'): GetFormEvent {
    return new GetFormEvent(RemoteFundingApplicationProcess::_getEntityName(), 'getForm', [
      'remoteContactId' => '00',
      'contactId' => 1,
      'applicationProcess' => $this->createApplicationProcess(),
      'fundingCase' => $this->createFundingCase(),
      'fundingProgram' => $this->createFundingProgram(),
      'fundingCaseType' => $this->createFundingCaseType($fundingCaseTypeName),
    ]);
  }

}
