<?php
declare(strict_types = 1);

namespace Civi\RemoteTools\Api4\Action;

use Civi\Api4\Generic\BasicGetFieldsAction;
use Civi\RemoteTools\Api4\Action\Traits\CreateActionEventTrait;
use Civi\RemoteTools\Api4\Action\Traits\EventActionTrait;
use Civi\RemoteTools\Event\GetFieldsEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class EventGetFieldsAction extends BasicGetFieldsAction implements EventActionInterface {

  use CreateActionEventTrait;

  use EventActionTrait;

  public function __construct(string $initRequestEventName, string $authorizeRequestEventName,
                              string $entityName, string $actionName = 'getFields',
                              EventDispatcherInterface $eventDispatcher = NULL) {
    parent::__construct($entityName, $actionName);
    $this->_initRequestEventName = $initRequestEventName;
    $this->_authorizeRequestEventName = $authorizeRequestEventName;
    $this->_eventDispatcher = $eventDispatcher ?? \Civi::dispatcher();
  }

  /**
   * @inerhitDoc
   *
   * @return array<array<string, array<string, scalar>|scalar[]|scalar|null>>
   *
   * @noinspection PhpMissingParentCallCommonInspection
   */
  public function fields(): array {
    return [];
  }

  /**
   * @inerhitDoc
   *
   * @return array<array<string, array<string, scalar>|scalar[]|scalar|null>>
   *
   * @noinspection PhpMissingParentCallCommonInspection
   */
  public function getRecords(): array {
    /** @var \Civi\RemoteTools\Event\GetFieldsEvent $event */
    $event = $this->createEvent();
    $this->dispatchEvent($event);

    return $event->getFields();
  }

  protected function getEventClass(): string {
    return GetFieldsEvent::class;
  }

}
