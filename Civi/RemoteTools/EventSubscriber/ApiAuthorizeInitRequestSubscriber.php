<?php
declare(strict_types = 1);

namespace Civi\RemoteTools\EventSubscriber;

use Civi\API\Event\AuthorizeEvent;
use Civi\Core\CiviEventDispatcher;
use Civi\RemoteTools\Api4\Action\EventActionInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class ApiAuthorizeInitRequestSubscriber implements EventSubscriberInterface {

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    // highest priority so that the init request event is dispatched before
    // the authorize event is actually handled
    return ['civi.api.authorize' => ['onApiAuthorize', PHP_INT_MAX]];
  }

  /**
   * @param \Civi\API\Event\AuthorizeEvent $event
   * @param string $eventName
   * @param \Civi\Core\CiviEventDispatcher $eventDispatcher
   *
   * @throws \API_Exception
   */
  public function onApiAuthorize(AuthorizeEvent $event, string $eventName, CiviEventDispatcher $eventDispatcher): void {
    $request = $event->getApiRequest();
    if ($request instanceof EventActionInterface) {
      $initRequestEvent = $request->getInitRequestEventClass()::fromApiRequest($request);
      $eventDispatcher->dispatch($request->getInitRequestEventName(), $initRequestEvent);
      $this->assertExtraParams($request);
    }
  }

  /**
   * @param \Civi\RemoteTools\Api4\Action\EventActionInterface $request
   *
   * @throws \API_Exception
   */
  private function assertExtraParams(EventActionInterface $request): void {
    foreach ($request->getRequiredExtraParams() as $key) {
      if (!$request->hasExtraParam($key)) {
        throw new \API_Exception(sprintf('Required extra param "%s" is missing', $key));
      }
    }
  }

}
