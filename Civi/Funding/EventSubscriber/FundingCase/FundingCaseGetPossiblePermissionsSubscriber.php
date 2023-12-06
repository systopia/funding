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
use Civi\Funding\ClearingProcess\ClearingProcessPermissions;
use Civi\RemoteTools\Event\GetPossiblePermissionsEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use CRM_Funding_ExtensionUtil as E;

final class FundingCaseGetPossiblePermissionsSubscriber implements EventSubscriberInterface {

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    return [GetPossiblePermissionsEvent::getName(FundingCase::getEntityName()) => 'onGetPossiblePermissions'];
  }

  public function onGetPossiblePermissions(GetPossiblePermissionsEvent $event): void {
    $event->addPermissions([
      'application_create' => E::ts('Application: create'),
      'application_view' => E::ts('Application: view'),
      'application_modify' => E::ts('Application: modify'),
      'application_apply' => E::ts('Application: apply'),
      'application_withdraw' => E::ts('Application: withdraw'),
      'application_request_rework' => E::ts('Application: request rework'),
      'drawdown_create' => E::ts('Drawdown: create'),
      'clearing_modify' => E::ts('Clearing: modify'),
      'clearing_apply' => E::ts('Clearing: apply'),
      'review_calculative' => E::ts('Review: calculative'),
      'review_content' => E::ts('Review: content'),
      'review_drawdown' => E::ts('Review: drawdown'),
      ClearingProcessPermissions::REVIEW_CALCULATIVE => E::ts('Review: clearing calculative'),
      ClearingProcessPermissions::REVIEW_CONTENT => E::ts('Review: clearing content'),
    ]);
  }

}
