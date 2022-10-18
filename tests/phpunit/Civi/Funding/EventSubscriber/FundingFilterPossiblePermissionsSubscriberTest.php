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

namespace Civi\Funding\EventSubscriber;

use Civi\Api4\FundingCase;
use Civi\Api4\FundingProgram;
use Civi\Funding\Util\SessionTestUtil;
use Civi\RemoteTools\Event\GetPossiblePermissionsEvent;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\EventSubscriber\FundingFilterPossiblePermissionsSubscriber
 */
final class FundingFilterPossiblePermissionsSubscriberTest extends TestCase {

  private FundingFilterPossiblePermissionsSubscriber $subscriber;

  protected function setUp(): void {
    parent::setUp();
    $this->subscriber = new FundingFilterPossiblePermissionsSubscriber();
  }

  protected function tearDown(): void {
    parent::tearDown();
    SessionTestUtil::resetSession();
  }

  public function testGetSubscribedEvents(): void {
    $expectedSubscriptions = [
      GetPossiblePermissionsEvent::getName(FundingCase::_getEntityName()) => ['onGetPossiblePermissions', PHP_INT_MIN],
      GetPossiblePermissionsEvent::getName(FundingProgram::_getEntityName())
      => ['onGetPossiblePermissions', PHP_INT_MIN],
    ];

    static::assertEquals($expectedSubscriptions, $this->subscriber::getSubscribedEvents());

    foreach ($expectedSubscriptions as [$method, $priority]) {
      static::assertTrue(method_exists(get_class($this->subscriber), $method));
    }
  }

  public function testOnGetPossiblePermissionsInternal(): void {
    $event = $this->createEvent();

    $this->subscriber->onGetPossiblePermissions($event);
    static::assertSame(['review_bar'], $event->getPermissions());
  }

  public function testOnGetPossiblePermissionsRemote(): void {
    SessionTestUtil::mockRemoteRequestSession('2');
    $event = $this->createEvent();

    $this->subscriber->onGetPossiblePermissions($event);
    static::assertSame(['application_foo'], $event->getPermissions());
  }

  private function createEvent(): GetPossiblePermissionsEvent {
    $event = new GetPossiblePermissionsEvent('entity');
    $event->setPermissions(['application_foo', 'review_bar']);

    return $event;
  }

}
