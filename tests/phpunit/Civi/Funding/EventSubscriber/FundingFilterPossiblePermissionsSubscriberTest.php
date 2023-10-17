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
use Civi\Funding\Mock\RequestContext\TestRequestContext;
use Civi\RemoteTools\Event\FilterPossiblePermissionsEvent;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\EventSubscriber\FundingFilterPossiblePermissionsSubscriber
 */
final class FundingFilterPossiblePermissionsSubscriberTest extends TestCase {

  private FundingFilterPossiblePermissionsSubscriber $subscriber;

  private TestRequestContext $requestContext;

  protected function setUp(): void {
    parent::setUp();
    $this->requestContext = TestRequestContext::newInternal();
    $this->subscriber = new FundingFilterPossiblePermissionsSubscriber($this->requestContext);
  }

  public function testGetSubscribedEvents(): void {
    $expectedSubscriptions = [
      FilterPossiblePermissionsEvent::getName(FundingCase::getEntityName())
      => ['onFilterPossiblePermissions', PHP_INT_MIN],
      FilterPossiblePermissionsEvent::getName(FundingProgram::getEntityName())
      => ['onFilterPossiblePermissions', PHP_INT_MIN],
    ];

    static::assertEquals($expectedSubscriptions, $this->subscriber::getSubscribedEvents());

    foreach ($expectedSubscriptions as [$method, $priority]) {
      static::assertTrue(method_exists(get_class($this->subscriber), $method));
    }
  }

  public function testOnFilterPossiblePermissionsInternal(): void {
    $event = new FilterPossiblePermissionsEvent('entity', ['application_foo' => 'Foo', 'review_bar' => 'Bar']);

    $this->subscriber->onFilterPossiblePermissions($event);
    static::assertSame(['review_bar' => 'Bar'], $event->getPermissions());
  }

  public function testOnFilterPossiblePermissionsRemote(): void {
    $this->requestContext->setRemote(TRUE);
    $event = new FilterPossiblePermissionsEvent('entity', ['application_foo' => 'Foo', 'review_bar' => 'Bar']);

    $this->subscriber->onFilterPossiblePermissions($event);
    static::assertSame(['application_foo' => 'Foo'], $event->getPermissions());
  }

}
