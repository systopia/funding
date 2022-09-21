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

namespace Civi\RemoteTools\EventSubscriber;

use Civi\Api4\Generic\AbstractAction;
use Civi\RemoteTools\Event\DAOGetEvent;
use Civi\RemoteTools\Event\GetEvent;

abstract class AbstractRemoteDAOGetSubscriber extends AbstractRemoteGetSubscriber {

  /**
   * Overwrite in subclasses if necessary
   *
   * @phpstan-var class-string<\Civi\RemoteTools\Event\DAOGetEvent>
   */
  protected const EVENT_CLASS = DAOGetEvent::class;

  /**
   * @inheritDoc
   */
  protected function createAction(GetEvent $event): AbstractAction {
    /** @var \Civi\RemoteTools\Event\DAOGetEvent $event */
    /*
     * Note: "where" could contain excluded fields, i.e. requester could use it
     * to detect values of excluded fields (if he knows the field name). Though
     * we trust the requester that he doesn't misuse it.
     */
    /** @var \Civi\Api4\Generic\DAOGetAction $action */
    $action = parent::createAction($event);

    return $action->setGroupBy($event->getGroupBy())
      ->setHaving($event->getHaving())
      ->setJoin($event->getJoin());
  }

}
