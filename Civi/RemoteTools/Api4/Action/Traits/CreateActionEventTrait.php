<?php
declare(strict_types = 1);

namespace Civi\RemoteTools\Api4\Action\Traits;

use Civi\RemoteTools\Event\AbstractRequestEvent;

trait CreateActionEventTrait {

  protected function createEvent(): AbstractRequestEvent {
    return $this->getEventClass()::fromApiRequest($this, $this->getExtraParams());
  }

  /**
   * @return class-string<\Civi\RemoteTools\Event\AbstractRequestEvent>
   */
  abstract protected function getEventClass(): string;

}
