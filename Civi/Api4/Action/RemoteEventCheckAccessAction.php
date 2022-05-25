<?php
declare(strict_types = 1);

namespace Civi\Api4\Action;

use Civi\Api4\Action\Traits\EventActionTrait;
use Civi\Api4\Action\Traits\RemoteActionTrait;
use Civi\Api4\Generic\CheckAccessAction;
use Civi\Api4\Generic\Result;
use Civi\Funding\Event\RemoteCheckAccessEvent;

class RemoteEventCheckAccessAction extends CheckAccessAction {

  use EventActionTrait;

  use RemoteActionTrait;

  public function __construct(string $entityName, string $actionName = 'checkAccess', EventDispatcherInterface $eventDispatcher = NULL) {
    parent::__construct($entityName, $actionName);
    $this->_eventDispatcher = $eventDispatcher ?? \Civi::dispatcher();
  }

  /**
   * @inheritDoc
   */
  public function _run(Result $result): void {
    parent::_run($result);

    if ($this->action !== 'checkAccess' && $result[0]['access']) {
      $this->checkRemoteAccessGranted($result);
    }
  }

  private function checkRemoteAccessGranted(Result $result): void {
    $event = RemoteCheckAccessEvent::fromApiRequest($this);
    $this->dispatchEvent($event);

    $result->debug['event'] = $event->getDebugOutput();
    $result->exchangeArray([['access' => FALSE !== $event->isAccessGranted()]]);
  }

}
