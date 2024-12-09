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

use Civi\Funding\Api4\Permissions;
use Civi\Funding\Event\FundingProgram\GetPermissionsEvent;
use Civi\RemoteTools\RequestContext\RequestContextInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Adds 'view' permission to every funding program for users with permission
 * Permissions::ADMINISTER_FUNDING, if the origin request is not remote.
 *
 * @codeCoverageIgnore
 */
final class FundingProgramPermissionsGetAdminSubscriber implements EventSubscriberInterface {

  private RequestContextInterface $requestContext;

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    return [GetPermissionsEvent::class => 'onPermissionsGet'];
  }

  public function __construct(RequestContextInterface $requestContext) {
    $this->requestContext = $requestContext;
  }

  public function onPermissionsGet(GetPermissionsEvent $event): void {
    if ($this->isFundingAdmin()) {
      $event->addPermissions(['view']);
    }
  }

  private function isFundingAdmin(): bool {
    if ($this->requestContext->isRemote()) {
      return FALSE;
    }

    if ($this->requestContext->getContactId() === 0) {
      // Grant access on CLI.
      return PHP_SAPI === 'cli';
    }

    return \CRM_Core_Permission::check(Permissions::ADMINISTER_FUNDING, $this->requestContext->getContactId());
  }

}
