<?php
declare(strict_types = 1);

namespace Civi\Api4\Action;

use Civi\Api4\Action\Traits\EventActionTrait;
use Civi\Api4\Action\Traits\RemoteActionContactIdRequiredTrait;
use Civi\Api4\Generic\AbstractGetAction;
use Civi\Api4\Generic\Result;
use Civi\Funding\Event\RemoteGetEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class RemoteEventGetAction extends AbstractGetAction {

  use EventActionTrait;

  use RemoteActionContactIdRequiredTrait;

  public function __construct(string $entityName, string $actionName = 'get', EventDispatcherInterface $eventDispatcher = NULL) {
    parent::__construct($entityName, $actionName);
    $this->_eventDispatcher = $eventDispatcher ?? \Civi::dispatcher();
  }

  /**
   * @inheritDoc
   */
  public function _run(Result $result): void {
    $event = RemoteGetEvent::fromApiRequest($this);
    $this->dispatchEvent($event);

    $result->debug['event'] = $event->getDebugOutput();
    $result->rowCount = $event->getRowCount();
    $result->exchangeArray($event->getRecords());
  }

}
