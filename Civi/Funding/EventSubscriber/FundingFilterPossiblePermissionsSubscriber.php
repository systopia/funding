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
use Civi\Funding\Util\SessionUtil;
use Civi\RemoteTools\Event\GetPossiblePermissionsEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class FundingFilterPossiblePermissionsSubscriber implements EventSubscriberInterface {

  private const APPLICATION_PERMISSION_PREFIX = 'application_';

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    return [
      GetPossiblePermissionsEvent::getName(FundingCase::_getEntityName()) => ['onGetPossiblePermissions', PHP_INT_MIN],
      GetPossiblePermissionsEvent::getName(FundingProgram::_getEntityName())
      => ['onGetPossiblePermissions', PHP_INT_MIN],
    ];
  }

  public function onGetPossiblePermissions(GetPossiblePermissionsEvent $event): void {
    if (SessionUtil::isRemoteSession(\CRM_Core_Session::singleton())) {
      $this->excludeNonApplicationPermissions($event);
    }
    else {
      $this->excludeApplicationPermissions($event);
    }
  }

  private function excludeApplicationPermissions(GetPossiblePermissionsEvent $event): void {
    $event->setPermissions(\array_filter(
        $event->getPermissions(),
        fn (string $permission) => !\str_starts_with($permission, self::APPLICATION_PERMISSION_PREFIX)
      )
    );
  }

  private function excludeNonApplicationPermissions(GetPossiblePermissionsEvent $event): void {
    $event->setPermissions(\array_filter(
        $event->getPermissions(),
        fn (string $permission) => \str_starts_with($permission, self::APPLICATION_PERMISSION_PREFIX)
      )
    );
  }

}
