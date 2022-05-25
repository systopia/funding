<?php
declare(strict_types = 1);

namespace Civi\Api4\Action;

use Civi\Api4\Action\Traits\EventActionTrait;
use Civi\Api4\Action\Traits\RemoteActionTrait;
use Civi\Api4\Generic\BasicGetFieldsAction;
use Civi\Funding\Event\RemoteGetFieldsEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class RemoteEventGetFieldsAction extends BasicGetFieldsAction {

  use EventActionTrait;

  use RemoteActionTrait;

  public function __construct(string $entityName, string $actionName = 'getFields', EventDispatcherInterface $eventDispatcher = NULL) {
    parent::__construct($entityName, $actionName);
    $this->_eventDispatcher = $eventDispatcher ?? \Civi::dispatcher();;
  }

  /**
   * @inerhitDoc
   */
  public function fields(): array {
    return [];
  }

  /**
   * @inerhitDoc
   */
  public function getRecords(): array {
    $event = RemoteGetFieldsEvent::fromApiRequest($this);
    $this->dispatchEvent($event);

    return $event->getFields();
  }

}
