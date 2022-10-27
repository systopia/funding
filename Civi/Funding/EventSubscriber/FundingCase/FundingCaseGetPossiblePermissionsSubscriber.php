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

use Civi\Api4\FundingCase;
use Civi\RemoteTools\Event\GetPossiblePermissionsEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class FundingCaseGetPossiblePermissionsSubscriber implements EventSubscriberInterface {

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    return [GetPossiblePermissionsEvent::getName(FundingCase::_getEntityName()) => 'onGetPossiblePermissions'];
  }

  public function onGetPossiblePermissions(GetPossiblePermissionsEvent $event): void {
    // TODO: Possible permissions for FundingCase
    $event->addPermissions([
      'application_view',
      'application_modify',
      'application_apply',
      'application_withdraw',
      'application_request_rework',
      'review_calculative',
      'review_content',
    ]);
  }

}
