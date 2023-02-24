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

namespace Civi\Funding\EventSubscriber\FundingProgram;

use Civi\Funding\Event\FundingProgram\GetPermissionsEvent;
use Civi\Funding\Mock\Session\TestFundingSession;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\EventSubscriber\FundingProgram\FundingProgramFilterPermissionsSubscriber
 */
final class FundingProgramFilterPermissionsSubscriberTest extends TestCase {

  private FundingProgramFilterPermissionsSubscriber $subscriber;

  private TestFundingSession $session;

  protected function setUp(): void {
    parent::setUp();
    $this->session = TestFundingSession::newInternal();
    $this->subscriber = new FundingProgramFilterPermissionsSubscriber($this->session);
  }

  public function testGetSubscribedEvents(): void {
    $expectedSubscriptions = [
      GetPermissionsEvent::class => ['onPermissionsGet', PHP_INT_MIN],
    ];

    static::assertEquals($expectedSubscriptions, FundingProgramFilterPermissionsSubscriber::getSubscribedEvents());

    foreach ($expectedSubscriptions as [$method, $priority]) {
      static::assertTrue(method_exists(FundingProgramFilterPermissionsSubscriber::class, $method));
    }
  }

  public function testOnPermissionsGetInternal(): void {
    $event = $this->createEvent();

    $this->subscriber->onPermissionsGet($event);
    static::assertSame(['review_bar'], $event->getPermissions());
  }

  public function testOnPermissionsGetRemote(): void {
    $this->session->setRemote(TRUE);
    $event = $this->createEvent();

    $this->subscriber->onPermissionsGet($event);
    static::assertSame(['application_foo'], $event->getPermissions());
  }

  private function createEvent(): GetPermissionsEvent {
    $event = new GetPermissionsEvent(1, 2);
    $event->setPermissions(['application_foo', 'review_bar']);

    return $event;
  }

}
