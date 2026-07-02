<?php
/*
 * Copyright (C) 2026 SYSTOPIA GmbH
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
use Civi\Core\Event\GenericHookEvent;
use CRM_Core_Permission;
use CRM_Funding_ExtensionUtil as E;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class FundingProgramSearchKitTaskSubscriber implements EventSubscriberInterface {

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    return ['hook_civicrm_searchKitTasks' => 'onSearchKitTasks'];
  }

  /**
   * @param \Civi\Core\Event\GenericHookEvent $event
   */
  public function onSearchKitTasks(GenericHookEvent $event): void {
    if ($event->checkPermissions
      && !CRM_Core_Permission::check('administer Funding', $event->userId)
    ) {
      return;
    }

    $event->tasks[FundingProgram::getEntityName()]['clone'] = [
      'title' => E::ts('Clone Funding Program'),
      'icon' => 'fa-copy',
      'apiBatch' => [
        'action' => 'clone',
        'runMsg' => E::ts('Cloning funding program...'),
        'successMsg' => E::ts('Successfully cloned funding program.'),
        'errorMsg' => E::ts('An error occurred while cloning funding program.'),
      ],
    ];
  }

}
