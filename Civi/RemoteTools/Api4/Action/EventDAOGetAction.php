<?php
declare(strict_types = 1);

namespace Civi\RemoteTools\Api4\Action;

use Civi\RemoteTools\Event\DAOGetEvent;

class EventDAOGetAction extends EventGetAction {

  /**
   * @noinspection PhpMissingParentCallCommonInspection
   */
  protected function getEventClass(): string {
    return DAOGetEvent::class;
  }

}
