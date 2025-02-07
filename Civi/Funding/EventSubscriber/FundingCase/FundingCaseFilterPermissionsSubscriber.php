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
use Civi\Funding\Permission\CiviPermissionChecker;
use Civi\RemoteTools\RequestContext\RequestContextInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class FundingCaseFilterPermissionsSubscriber implements EventSubscriberInterface {

  private const APPLICANT_PERMISSION_PREFIXES = [
    'application_',
    'drawdown_',
    'clearing_',
  ];

  private CiviPermissionChecker $permissionChecker;

  private RequestContextInterface $requestContext;

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    return [
      GetPermissionsEvent::class => ['onPermissionsGet', PHP_INT_MIN],
    ];
  }

  public function __construct(CiviPermissionChecker $permissionChecker, RequestContextInterface $requestContext) {
    $this->permissionChecker = $permissionChecker;
    $this->requestContext = $requestContext;
  }

  public function onPermissionsGet(GetPermissionsEvent $event): void {
    if ($this->requestContext->isRemote()) {
      $this->provideOnlyApplicantPermissions($event);
    }
    else {
      $this->preventAccessIfHasApplicantPermission($event);
    }
  }

  private function isApplicantPermission(string $permission): bool {
    foreach (self::APPLICANT_PERMISSION_PREFIXES as $permissionPrefix) {
      if (\str_starts_with($permission, $permissionPrefix)) {
        return TRUE;
      }
    }

    return FALSE;
  }

  private function provideOnlyApplicantPermissions(GetPermissionsEvent $event): void {
    $event->setPermissions(\array_filter(
      $event->getPermissions(),
      fn (string $permission) => $this->isApplicantPermission($permission),
    ));
  }

  private function preventAccessIfHasApplicantPermission(GetPermissionsEvent $event): void {
    foreach ($event->getPermissions() as $permission) {
      if ($this->isApplicantPermission($permission)) {
        // funding admins are still allowed to view the application.
        $event->setPermissions($this->isFundingAdmin() ? ['view'] : []);

        return;
      }
    }
  }

  private function isFundingAdmin(): bool {
    return $this->permissionChecker->checkPermission(Permissions::ADMINISTER_FUNDING);
  }

}
