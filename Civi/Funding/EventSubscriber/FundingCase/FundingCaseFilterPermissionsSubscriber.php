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
use Civi\Funding\Session\FundingSessionInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class FundingCaseFilterPermissionsSubscriber implements EventSubscriberInterface {

  private const APPLICANT_PERMISSION_PREFIXES = [
    'application_',
    'drawdown_',
  ];

  private FundingSessionInterface $session;

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    return [
      GetPermissionsEvent::class => ['onPermissionsGet', PHP_INT_MIN],
    ];
  }

  public function __construct(FundingSessionInterface $session) {
    $this->session = $session;
  }

  public function onPermissionsGet(GetPermissionsEvent $event): void {
    if ($this->session->isRemote()) {
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
        $event->setPermissions([]);

        return;
      }
    }
  }

}
