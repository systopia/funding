<?php
declare(strict_types = 1);

namespace Civi\Api4\Action\Traits;

use Civi\Funding\Event\AbstractApiEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

trait EventActionTrait {

  protected EventDispatcherInterface $_eventDispatcher;

  protected function dispatchEvent(AbstractApiEvent $event): void {
    /** @var \Civi\Api4\Generic\AbstractAction $this */
    $this->_eventDispatcher->dispatch($event::getEventName($this->getEntityName(), $this->getActionName()), $event);
    $this->_eventDispatcher->dispatch($event::getEventName($this->getEntityName()), $event);
    $this->_eventDispatcher->dispatch($event::getEventName(), $event);
  }

}
