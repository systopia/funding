<?php
declare(strict_types = 1);

namespace Civi\RemoteTools\EventSubscriber;

use Civi\API\Event\AuthorizeEvent;
use Civi\API\Events;
use Civi\Api4\Generic\AbstractAction;
use Civi\RemoteTools\Api4\Action\EventActionInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ApiAuthorizeSubscriber implements EventSubscriberInterface {

  public static function getSubscribedEvents(): array {
    return [
      'civi.api.authorize' => ['onApiAuthorize', Events::W_EARLY],
    ];
  }

  public function onApiAuthorize(AuthorizeEvent $event, string $eventName, EventDispatcherInterface $eventDispatcher): void {
    $request = $event->getApiRequest();
    if (!$request instanceof EventActionInterface) {
      return;
    }

    $authorized = $this->isApiRequestAuthorized($request, $eventDispatcher);
    if (NULL !== $authorized) {
      $event->setAuthorized($authorized);
      $event->stopPropagation();
    }
  }

  protected function isApiRequestAuthorized(AbstractAction $request, EventDispatcherInterface $eventDispatcher): ?bool {
    if (!$request instanceof EventActionInterface) {
      return FALSE;
    }

    /** @var \Civi\RemoteTools\Event\AuthorizeApiRequestEvent $authorizeRequestEvent */
    $authorizeRequestEvent = $request->getAuthorizeRequestEventClass()::fromApiRequest($request);
    $eventDispatcher->dispatch($request->getAuthorizeRequestEventName(), $authorizeRequestEvent);

    return $authorizeRequestEvent->isAuthorized();
  }

}
