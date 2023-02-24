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
use Civi\Funding\Session\FundingSessionInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class FundingProgramFilterPermissionsSubscriber implements EventSubscriberInterface {

  private const APPLICATION_PERMISSION_PREFIX = 'application_';

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
      $this->excludeNonApplicationPermissions($event);
    }
    else {
      $this->excludeApplicationPermissions($event);
    }
  }

  private function excludeApplicationPermissions(GetPermissionsEvent $event): void {
    $event->setPermissions(\array_filter(
        $event->getPermissions(),
        fn (string $permission) => !\str_starts_with($permission, self::APPLICATION_PERMISSION_PREFIX)
      )
    );
  }

  private function excludeNonApplicationPermissions(GetPermissionsEvent $event): void {
    $event->setPermissions(\array_filter(
        $event->getPermissions(),
        fn (string $permission) => \str_starts_with($permission, self::APPLICATION_PERMISSION_PREFIX)
      )
    );
  }

}
