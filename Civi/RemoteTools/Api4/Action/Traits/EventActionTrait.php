<?php
declare(strict_types = 1);

namespace Civi\RemoteTools\Api4\Action\Traits;

use Civi\RemoteTools\Event\AbstractRequestEvent;
use Civi\RemoteTools\Event\AuthorizeApiRequestEvent;
use Civi\RemoteTools\Event\InitApiRequestEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

trait EventActionTrait {

  protected string $_authorizeRequestEventName;

  /**
   * @var array<string, mixed>
   */
  protected array $_extraParams = [];

  protected EventDispatcherInterface $_eventDispatcher;

  protected string $_initRequestEventName;

  /**
   * @noinspection PhpParamsInspection
   */
  protected function dispatchEvent(AbstractRequestEvent $event): void {
    /** @var \Civi\Api4\Generic\AbstractAction $this */
    $this->_eventDispatcher->dispatch($event::getEventName($this->getEntityName(), $this->getActionName()), $event);
    $this->_eventDispatcher->dispatch($event::getEventName($this->getEntityName()), $event);
    $this->_eventDispatcher->dispatch($event::getEventName(), $event);
  }

  public function getAuthorizeRequestEventClass(): string {
    return AuthorizeApiRequestEvent::class;
  }

  public function getAuthorizeRequestEventName(): string {
    return $this->_authorizeRequestEventName;
  }

  public function getInitRequestEventClass(): string {
    return InitApiRequestEvent::class;
  }

  public function getInitRequestEventName(): string {
    return $this->_initRequestEventName;
  }

  public function getExtraParam(string $key) {
    return $this->_extraParams[$key] ?? NULL;
  }

  public function getExtraParams(): array {
    return $this->_extraParams;
  }

  public function hasExtraParam(string $key): bool {
    return array_key_exists($key, $this->_extraParams);
  }

  public function setExtraParam(string $key, $value): self {
    $this->_extraParams[$key] = $value;

    return $this;
  }

  public function setExtraParams(array $extraParams): self {
    $this->_extraParams = $extraParams;

    return $this;
  }

  public function getRequiredExtraParams(): array {
    return [];
  }

}
