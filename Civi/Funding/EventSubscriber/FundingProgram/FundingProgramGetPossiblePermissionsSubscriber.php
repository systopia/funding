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

use Civi\Api4\FundingProgram;
use Civi\RemoteTools\Event\GetPossiblePermissionsEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use CRM_Funding_ExtensionUtil as E;

final class FundingProgramGetPossiblePermissionsSubscriber implements EventSubscriberInterface {

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    return [GetPossiblePermissionsEvent::getName(FundingProgram::_getEntityName()) => 'onGetPossiblePermissions'];
  }

  public function onGetPossiblePermissions(GetPossiblePermissionsEvent $event): void {
    // TODO: Possible permissions for FundingProgram
    $event->addPermissions([
      'application_create' => E::ts('Application: create'),
      'application_apply' => E::ts('Application: apply'),
      'view' => E::ts('View'),
    ]);
  }

}
