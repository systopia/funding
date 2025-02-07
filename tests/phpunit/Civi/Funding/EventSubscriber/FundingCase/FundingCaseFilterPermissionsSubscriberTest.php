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

use Civi\Funding\Api4\Permissions;
use Civi\Funding\Event\FundingCase\GetPermissionsEvent;
use Civi\Funding\Mock\RequestContext\TestRequestContext;
use Civi\Funding\Permission\CiviPermissionChecker;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\EventSubscriber\FundingCase\FundingCaseFilterPermissionsSubscriber
 */
final class FundingCaseFilterPermissionsSubscriberTest extends TestCase {

  /**
   * @var \Civi\Funding\Permission\CiviPermissionChecker&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $permissionCheckerMock;

  private FundingCaseFilterPermissionsSubscriber $subscriber;

  private TestRequestContext $requestContext;

  protected function setUp(): void {
    parent::setUp();
    $this->permissionCheckerMock = $this->createMock(CiviPermissionChecker::class);
    $this->requestContext = TestRequestContext::newInternal();
    $this->subscriber = new FundingCaseFilterPermissionsSubscriber($this->permissionCheckerMock, $this->requestContext);
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

  public function testExcludesNonApplicantPermissionsRemoteRequest(): void {
    $this->requestContext->setRemote(TRUE);
    $event = $this->createEvent(['application_foo', 'drawdown_foo', 'review_bar']);

    $this->subscriber->onPermissionsGet($event);
    static::assertSame(['application_foo', 'drawdown_foo'], $event->getPermissions());
  }

  public function testPreventAccessInternalRequest(): void {
    $event = $this->createEvent(['application_foo', 'review_bar', 'view']);

    $this->subscriber->onPermissionsGet($event);
    static::assertSame([], $event->getPermissions());
  }

  public function testFundingAdminCanViewCasesWithApplicantPermissions(): void {
    $event = $this->createEvent(['application_foo', 'review_bar', 'view']);
    $this->permissionCheckerMock->method('checkPermission')
      ->with(Permissions::ADMINISTER_FUNDING)
      ->willReturn(TRUE);

    $this->subscriber->onPermissionsGet($event);
    static::assertSame(['view'], $event->getPermissions());
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
