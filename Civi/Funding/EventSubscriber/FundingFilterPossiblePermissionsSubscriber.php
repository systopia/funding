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
use Civi\RemoteTools\Event\FilterPossiblePermissionsEvent;
use Civi\RemoteTools\RequestContext\RequestContextInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class FundingFilterPossiblePermissionsSubscriber implements EventSubscriberInterface {

  private const APPLICATION_PERMISSION_PREFIX = 'application_';

  private RequestContextInterface $requestContext;

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    return [
      FilterPossiblePermissionsEvent::getName(FundingCase::_getEntityName())
      => ['onFilterPossiblePermissions', PHP_INT_MIN],
      FilterPossiblePermissionsEvent::getName(FundingProgram::_getEntityName())
      => ['onFilterPossiblePermissions', PHP_INT_MIN],
    ];
  }

  public function __construct(RequestContextInterface $requestContext) {
    $this->requestContext = $requestContext;
  }

  public function onFilterPossiblePermissions(FilterPossiblePermissionsEvent $event): void {
    if ($this->requestContext->isRemote()) {
      $this->excludeNonApplicationPermissions($event);
    }
    else {
      $this->excludeApplicationPermissions($event);
    }
  }

  private function excludeApplicationPermissions(FilterPossiblePermissionsEvent $event): void {
    $event->removePermissionsByPrefix(self::APPLICATION_PERMISSION_PREFIX);
  }

  private function excludeNonApplicationPermissions(FilterPossiblePermissionsEvent $event): void {
    $event->keepPermissionsByPrefix(self::APPLICATION_PERMISSION_PREFIX);
  }

}
