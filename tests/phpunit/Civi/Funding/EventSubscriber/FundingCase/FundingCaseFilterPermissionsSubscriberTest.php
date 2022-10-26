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

namespace Civi\Funding\EventSubscriber\FundingCase;

use Civi\Funding\Event\FundingCase\GetPermissionsEvent;
use Civi\Funding\Util\SessionTestUtil;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\EventSubscriber\FundingCase\FundingCaseFilterPermissionsSubscriber
 */
final class FundingCaseFilterPermissionsSubscriberTest extends TestCase {

  private FundingCaseFilterPermissionsSubscriber $subscriber;

  protected function setUp(): void {
    parent::setUp();
    $this->subscriber = new FundingCaseFilterPermissionsSubscriber();
  }

  protected function tearDown(): void {
    parent::tearDown();
    SessionTestUtil::resetSession();
  }

  public function testGetSubscribedEvents(): void {
    $expectedSubscriptions = [
      GetPermissionsEvent::class => ['onPermissionsGet', PHP_INT_MIN],
    ];

    static::assertEquals($expectedSubscriptions, FundingCaseFilterPermissionsSubscriber::getSubscribedEvents());

    foreach ($expectedSubscriptions as [$method, $priority]) {
      static::assertTrue(method_exists(FundingCaseFilterPermissionsSubscriber::class, $method));
    }
  }

  public function testExcludesNonApplicationPermissionsRemoteRequest(): void {
    SessionTestUtil::mockRemoteRequestSession('2');
    $event = $this->createEvent(['application_foo', 'review_bar']);

    $this->subscriber->onPermissionsGet($event);
    static::assertSame(['application_foo'], $event->getPermissions());
  }

  public function testPreventAccessInternalRequest(): void {
    $event = $this->createEvent(['application_foo', 'review_bar']);

    $this->subscriber->onPermissionsGet($event);
    static::assertSame([], $event->getPermissions());
  }

  public function testInternalRequest(): void {
    $event = $this->createEvent(['review_bar']);

    $this->subscriber->onPermissionsGet($event);
    static::assertSame(['review_bar'], $event->getPermissions());
  }

  /**
   * @phpstan-param array<string> $permissions
   */
  private function createEvent(array $permissions): GetPermissionsEvent {
    $event = new GetPermissionsEvent(1, 2);
    $event->setPermissions($permissions);

    return $event;
  }

}
