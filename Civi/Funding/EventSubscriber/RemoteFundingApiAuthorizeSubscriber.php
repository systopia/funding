<?php
declare(strict_types = 1);

namespace Civi\Funding\EventSubscriber;

use Civi\API\Events;
use Civi\Funding\Event\RemoteFundingCheckAccessEvent;
use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\EventSubscriber\ApiAuthorizeSubscriber;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class RemoteFundingApiAuthorizeSubscriber extends ApiAuthorizeSubscriber {

  private Api4Interface $api4;

  public function __construct(Api4Interface $api4) {
    $this->api4 = $api4;
  }

  public static function getSubscribedEvents(): array {
    return [
      RemoteFundingCheckAccessEvent::getEventName() => ['onCheckAccess', Events::W_EARLY],
    ];
  }

  /**
   * @throws \Civi\API\Exception\NotImplementedException
   */
  public function onCheckAccess(RemoteFundingCheckAccessEvent $event, string $eventName, EventDispatcherInterface $eventDispatcher): void {
    $event->addDebugOutput(__CLASS__, []);
    $params = [];
    if (NULL !== $event->getRemoteContactId()) {
      $params['remoteContactId'] = $event->getRemoteContactId();
    }
    $apiRequest = $this->api4->createAction($event->getEntityName(), $event->getAction(), $params);

    if (FALSE === $this->isApiRequestAuthorized($apiRequest, $eventDispatcher) || FALSE === $this->isAccessRecordGranted($event)) {
      $event->setAccessGranted(FALSE);
      $event->stopPropagation();
    }
  }

  /**
   * TODO
   *
   * @see \Civi\Api4\Utils\CoreUtil::checkAccessRecord()
   */
  private function isAccessRecordGranted(RemoteFundingCheckAccessEvent $event): ?bool {
    return NULL;
  }

}
