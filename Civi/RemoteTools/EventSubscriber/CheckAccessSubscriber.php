<?php
declare(strict_types = 1);

namespace Civi\RemoteTools\EventSubscriber;

use Civi\API\Events;
use Civi\RemoteTools\Api4\Action\EventActionInterface;
use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\Event\CheckAccessEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class CheckAccessSubscriber extends ApiAuthorizeSubscriber {

  protected Api4Interface $api4;

  /**
   * @noinspection PhpMissingParentCallCommonInspection
   */
  public static function getSubscribedEvents(): array {
    return [
      CheckAccessEvent::getEventName() => ['onCheckAccess', Events::W_EARLY],
    ];
  }

  public function __construct(Api4Interface $api4) {
    $this->api4 = $api4;
  }

  /**
   * @throws \Civi\API\Exception\NotImplementedException
   */
  public function onCheckAccess(CheckAccessEvent $event, string $eventName, EventDispatcherInterface $eventDispatcher): void {
    $event->addDebugOutput(static::class, []);
    $apiRequest = $this->api4->createAction($event->getEntityName(), $event->getAction(), $event->getRequestParams());

    if ($apiRequest instanceof EventActionInterface) {
      $initRequestEvent = $apiRequest->getInitRequestEventClass()::fromApiRequest($apiRequest);
      $eventDispatcher->dispatch($apiRequest->getInitRequestEventName(), $initRequestEvent);
    }

    if (FALSE === $this->isApiRequestAuthorized($apiRequest, $eventDispatcher)) {
      $event->setAccessGranted(FALSE);
      $event->stopPropagation();
    }
  }

}
