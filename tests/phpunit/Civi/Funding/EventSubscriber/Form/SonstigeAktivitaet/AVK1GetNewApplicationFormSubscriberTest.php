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

use Civi\Funding\Entity\FundingProgramEntity;
use Civi\Funding\Event\Remote\FundingCase\GetNewApplicationFormEvent;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\EventSubscriber\Form\SonstigeAktivitaet\AVK1GetNewApplicationFormSubscriber
 */
final class AVK1GetNewApplicationFormSubscriberTest extends TestCase {

  public function testGetSubscribedEvents(): void {
    $expectedSubscriptions = [
      GetNewApplicationFormEvent::getEventName() => 'onGetNewForm',
    ];

    static::assertEquals($expectedSubscriptions, AVK1GetNewApplicationFormSubscriber::getSubscribedEvents());

    foreach ($expectedSubscriptions as $method) {
      static::assertTrue(method_exists(AVK1GetNewApplicationFormSubscriber::class, $method));
    }
  }

  public function testOnGetNewForm(): void {
    $subscriber = new AVK1GetNewApplicationFormSubscriber();
    $event = $this->createEvent();
    $subscriber->onGetNewForm($event);

    static::assertNotNull($event->getJsonSchema());
    static::assertNotNull($event->getUiSchema());
    static::assertArrayHasKey('fundingProgramId', $event->getData());
    static::assertSame(2, $event->getData()['fundingProgramId']);
    static::assertArrayHasKey('fundingCaseTypeId', $event->getData());
    static::assertSame(3, $event->getData()['fundingCaseTypeId']);
  }

  public function testOnGetNewFormFundingCaseTypeNotMatch(): void {
    $subscriber = new AVK1GetNewApplicationFormSubscriber();
    $event = $this->createEvent('Foo');
    $subscriber->onGetNewForm($event);

    static::assertSame([], $event->getData());
    static::assertNull($event->getJsonSchema());
    static::assertNull($event->getUiSchema());
  }

  private function createEvent(string $fundingCaseTypeName = 'AVK1SonstigeAktivitaet'): GetNewApplicationFormEvent {
    return new GetNewApplicationFormEvent('RemoteFundingCase', 'GetNewApplicationForm', [
      'remoteContactId' => '00',
      'contactId' => 1,
      'fundingProgram' => $this->createFundingProgram(),
      'fundingCaseType' => ['id' => 3, 'name' => $fundingCaseTypeName],
    ]);
  }

  private function createFundingProgram(): FundingProgramEntity {
    return FundingProgramEntity::fromArray([
      'id' => 2,
      'title' => 'TestFundingProgram',
      'start_date' => '2022-10-22',
      'end_date' => '2023-10-22',
      'requests_start_date' => '2022-06-22',
      'requests_end_date' => '2022-12-31',
      'budget' => NULL,
      'currency' => '€',
    ]);
  }

}
