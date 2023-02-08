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

namespace Civi\Funding\EventSubscriber\Remote;

use Civi\Api4\Generic\AbstractAction;
use Civi\Funding\ActivityTypeIds;
use Civi\RemoteTools\Event\GetEvent;
use Civi\RemoteTools\EventSubscriber\AbstractRemoteGetSubscriber;

class ApplicationProcessActivityGetSubscriber extends AbstractRemoteGetSubscriber {

  protected const BASIC_ENTITY_NAME = 'FundingApplicationProcessActivity';

  protected const ENTITY_NAME = 'RemoteFundingApplicationProcessActivity';

  protected const EVENT_CLASS = \Civi\Funding\Event\Remote\ApplicationProcessActivity\GetEvent::class;

  protected function createAction(GetEvent $event): AbstractAction {
    /** @var \Civi\Funding\Event\Remote\ApplicationProcessActivity\GetEvent $event */
    /** @var \Civi\Funding\Api4\Action\Remote\ApplicationProcessActivity\GetAction $action */
    $action = parent::createAction($event);

    return $action
      ->setApplicationProcessId($event->getApplicationProcessId())
      ->addClause('OR',
        ['activity_type_id', '=', ActivityTypeIds::FUNDING_APPLICATION_CREATE],
        ['activity_type_id', '=', ActivityTypeIds::FUNDING_APPLICATION_STATUS_CHANGE],
        ['activity_type_id', '=', ActivityTypeIds::FUNDING_APPLICATION_COMMENT_EXTERNAL],
      );
  }

}
