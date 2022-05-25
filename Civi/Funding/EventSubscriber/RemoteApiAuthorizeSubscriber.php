<?php
declare(strict_types = 1);

namespace Civi\Funding\EventSubscriber;

use Civi\API\Event\AuthorizeEvent;
use Civi\API\Events;
use Civi\Api4\Generic\AbstractAction;
use Civi\Api4\Generic\Api4Interface;
use Civi\Funding\Event\RemoteCheckAccessEvent;
use Civi\Funding\Contact\RemoteContactIdResolverInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RemoteApiAuthorizeSubscriber implements EventSubscriberInterface {

  private Api4Interface $api4;

  private RemoteContactIdResolverInterface $remoteContactIdResolver;

  public function __construct(Api4Interface $api4, RemoteContactIdResolverInterface $remoteContactIdResolver) {
    $this->api4 = $api4;
    $this->remoteContactIdResolver = $remoteContactIdResolver;
  }

  public static function getSubscribedEvents() {
    return [
      'civi.api.authorize' => ['onApiAuthorize', Events::W_EARLY],
      RemoteCheckAccessEvent::getEventName() => ['onCheckAccess', Events::W_EARLY],
    ];
  }

  public function onApiAuthorize(AuthorizeEvent $event): void {
    if (is_array($event->getApiRequest())) {
      return;
    }

    if (!$this->isApiRequestGranted($event->getApiRequest())) {
      $event->setAuthorized(FALSE);
      $event->stopPropagation();
    }
  }

  public function onCheckAccess(RemoteCheckAccessEvent $event): void {
    $event->addDebugOutput(__CLASS__, []);
    $apiRequest = $this->api4->createAction($event->getEntityName(), $event->getAction(), ['version' => 4, 'remoteContactId' => $event->getRemoteContactId()]);

    if (!$this->isApiRequestGranted($apiRequest) || FALSE === $this->isAccessRecordGranted($event)) {
      $event->setAccessGranted(FALSE);
      $event->stopPropagation();
    }
  }

  private function isApiRequestGranted(AbstractAction $apiRequest): bool {
    $remoteContactId = $this->getRemoteContactId($apiRequest);
    if (NULL === $remoteContactId) {
      return !$this->isRemoteContactIdRequired($apiRequest);
    }

    $userId = $this->remoteContactIdResolver->getUFId($remoteContactId);
    if (NULL === $userId) {
      return FALSE;
    }

    // TODO: Use injected service instead of static method
    return \CRM_Core_Permission::check($apiRequest->getPermissions(), $this->remoteContactIdResolver->getContactId($remoteContactId));
  }

  /**
   * @param \Civi\Api4\Generic\AbstractAction $apiRequest
   *
   * @return int|string|null
   */
  private function getRemoteContactId(AbstractAction $apiRequest) {
    if (method_exists($apiRequest, 'getRemoteContactId')) {
      return $apiRequest->getRemoteContactId();
    }

    return NULL;
  }

  /**
   * TODO
   *
   * @see \Civi\Api4\Utils\CoreUtil::checkAccessRecord()
   */
  private function isAccessRecordGranted(RemoteCheckAccessEvent $event): ?bool {
    return NULL;
  }

  private function isRemoteContactIdRequired(AbstractAction $apiRequest): bool {
    return $apiRequest->paramExists('remoteContactId')
      && ($apiRequest->getParamInfo('remoteContactId')['required'] ?? FALSE);
  }

}
