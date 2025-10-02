<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
 *
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License as published by the Free
 * Software Foundation, either version 3 of the License, or (at your option) any
 * later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types = 1);

namespace Civi\Funding\Translation\EventSubscriber;

use Civi\Api4\FundingCaseTypeProgram;
use Civi\Core\Event\GenericHookEvent;
use CRM_Funding_ExtensionUtil as E;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class ExtractStringsSearchKitTaskSubscriber implements EventSubscriberInterface {

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    return ['hook_civicrm_searchKitTasks' => 'onSearchKitTasks'];
  }

  public function onSearchKitTasks(GenericHookEvent $event): void {
    if ($event->checkPermissions
      && !\CRM_Core_Permission::check(FundingCaseTypeProgram::permissions()['extractStrings'], $event->userId)
    ) {
      return;
    }

    $event->tasks[FundingCaseTypeProgram::getEntityName()]['extractStrings'] = [
      'title' => E::ts('Extract Strings'),
      'apiBatch' => [
        'action' => 'extractStrings',
        'runMsg' => E::ts('Extract strings...'),
        'successMsg' => E::ts('Successfully extracted strings.'),
        'errorMsg' => E::ts('An error occurred while extracting strings.'),
      ],
    ];
  }

}
