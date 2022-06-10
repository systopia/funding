<?php
declare(strict_types = 1);

namespace Civi\RemoteTools\Api4\Action;

use Civi\Api4\Generic\CheckAccessAction;
use Civi\Api4\Generic\Result;
use Civi\RemoteTools\Api4\Action\Traits\CreateActionEventTrait;
use Civi\RemoteTools\Api4\Action\Traits\EventActionTrait;
use Civi\RemoteTools\Event\CheckAccessEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Webmozart\Assert\Assert;

class EventCheckAccessAction extends CheckAccessAction implements EventActionInterface {

  use CreateActionEventTrait;

  use EventActionTrait;

  public function __construct(string $initRequestEventName, string $authorizeRequestEventName,
                              string $entityName, string $actionName = 'checkAccess',
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
    parent::_run($result);

    Assert::isArray($result[0]);
    if (!in_array($this->action, ['checkAccess', 'getActions'], TRUE) && $result[0]['access']) {
      $this->checkRemoteAccessGranted($result);
    }
  }

  /**
   * @inheritDoc
   */
  protected function getEventClass(): string {
    return CheckAccessEvent::class;
  }

  private function checkRemoteAccessGranted(Result $result): void {
    /** @var \Civi\RemoteTools\Event\CheckAccessEvent $event */
    $event = $this->createEvent();

    if (CheckAccessEvent::class !== get_class($event)) {
      /** @noinspection PhpParamsInspection */
      $this->_eventDispatcher->dispatch(CheckAccessEvent::getEventName(), $event);
    }
    $this->dispatchEvent($event);

    $result->debug['event'] = $event->getDebugOutput();
    $result->exchangeArray([['access' => FALSE !== $event->isAccessGranted()]]);
  }

}
