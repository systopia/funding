<?php
declare(strict_types = 1);

namespace Civi\RemoteTools\Api4\Action;

use Civi\Api4\Generic\AbstractGetAction;
use Civi\Api4\Generic\Result;
use Civi\RemoteTools\Api4\Action\Traits\CreateActionEventTrait;
use Civi\RemoteTools\Api4\Action\Traits\EventActionTrait;
use Civi\RemoteTools\Event\GetEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class EventGetAction extends AbstractGetAction implements EventActionInterface {

  use CreateActionEventTrait;

  use EventActionTrait;

  public function __construct(string $initRequestEventName, string $authorizeRequestEventName,
                              string $entityName, string $actionName = 'get',
                              EventDispatcherInterface $eventDispatcher = NULL) {
    parent::__construct($entityName, $actionName);
    $this->_initRequestEventName = $initRequestEventName;
    $this->_authorizeRequestEventName = $authorizeRequestEventName;
    $this->_eventDispatcher = $eventDispatcher ?? \Civi::dispatcher();
  }

  /**
   * @inheritDoc
   */
  public function _run(Result $result): void {
    /** @var \Civi\RemoteTools\Event\GetEvent $event */
    $event = $this->createEvent();
    $this->dispatchEvent($event);

    $result->debug['event'] = $event->getDebugOutput();
    $result->rowCount = $event->getRowCount();
    $result->exchangeArray($event->getRecords());
  }

  protected function getEventClass(): string {
    return GetEvent::class;
  }

}
