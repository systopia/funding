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
use Civi\Funding\PermissionPrefixes;
use Civi\RemoteTools\Event\FilterPossiblePermissionsEvent;
use Civi\RemoteTools\RequestContext\RequestContextInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class FundingFilterPossiblePermissionsSubscriber implements EventSubscriberInterface {

  private RequestContextInterface $requestContext;

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    return [
      FilterPossiblePermissionsEvent::getName(FundingCase::getEntityName())
      => ['onFilterPossiblePermissions', PHP_INT_MIN],
      FilterPossiblePermissionsEvent::getName(FundingProgram::getEntityName())
      => ['onFilterPossiblePermissions', PHP_INT_MIN],
    ];
  }

  public function __construct(RequestContextInterface $requestContext) {
    $this->requestContext = $requestContext;
  }

  public function onFilterPossiblePermissions(FilterPossiblePermissionsEvent $event): void {
    if ($this->requestContext->isRemote()) {
      $this->excludeNonApplicantPermissions($event);
    }
    else {
      $this->excludeApplicantPermissions($event);
    }
  }

  private function excludeApplicantPermissions(FilterPossiblePermissionsEvent $event): void {
    array_map(
      fn (string $prefix) => $event->removePermissionsByPrefix($prefix),
      PermissionPrefixes::APPLICANT
    );
  }

  private function excludeNonApplicantPermissions(FilterPossiblePermissionsEvent $event): void {
    $event->keepPermissionsByPrefixes(PermissionPrefixes::APPLICANT);
  }

}
