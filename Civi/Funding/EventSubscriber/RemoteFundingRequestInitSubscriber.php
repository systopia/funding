<?php
declare(strict_types = 1);

namespace Civi\Funding\EventSubscriber;

use Civi\Funding\Api4\Action\RemoteFundingActionInterface;
use Civi\Funding\Contact\FundingRemoteContactIdResolver;
use Civi\Funding\Event\FundingEvents;
use Civi\RemoteTools\Event\InitApiRequestEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Webmozart\Assert\Assert;

final class RemoteFundingRequestInitSubscriber implements EventSubscriberInterface {

  private FundingRemoteContactIdResolver $remoteContactIdResolver;

  public function __construct(FundingRemoteContactIdResolver $remoteContactIdResolver) {
    $this->remoteContactIdResolver = $remoteContactIdResolver;
  }

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    return [FundingEvents::REMOTE_REQUEST_INIT_EVENT_NAME => 'onRemoteRequestInit'];
  }

  public function onRemoteRequestInit(InitApiRequestEvent $event): void {
    $request = $event->getApiRequest();
    Assert::isInstanceOf($request, RemoteFundingActionInterface::class);
    /** @var \Civi\Funding\Api4\Action\RemoteFundingActionInterface $request */
    $remoteContactId = $request->getRemoteContactId();
    if (NULL !== $remoteContactId) {
      $request->setExtraParam('contactId', $this->remoteContactIdResolver->getContactId($remoteContactId));
    }
  }

}
