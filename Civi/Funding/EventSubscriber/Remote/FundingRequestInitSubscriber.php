<?php
/*
 * Copyright (C) 2022 SYSTOPIA GmbH
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as published by
 *  the Free Software Foundation in version 3.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types = 1);

namespace Civi\Funding\EventSubscriber\Remote;

use Civi\Funding\Api4\Action\Remote\GetFieldsAction;
use Civi\Funding\Api4\Action\Remote\RemoteFundingActionInterface;
use Civi\Funding\Contact\FundingRemoteContactIdResolverInterface;
use Civi\Funding\Event\Remote\FundingEvents;
use Civi\RemoteTools\Event\InitApiRequestEvent;
use Civi\RemoteTools\RequestContext\RequestContextInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Webmozart\Assert\Assert;

class FundingRequestInitSubscriber implements EventSubscriberInterface {

  private FundingRemoteContactIdResolverInterface $remoteContactIdResolver;

  private RequestContextInterface $requestContext;

  public function __construct(
    FundingRemoteContactIdResolverInterface $remoteContactIdResolver,
    RequestContextInterface $requestContext
  ) {
    $this->remoteContactIdResolver = $remoteContactIdResolver;
    $this->requestContext = $requestContext;
  }

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    return [FundingEvents::REQUEST_INIT_EVENT_NAME => 'onRemoteRequestInit'];
  }

  public function onRemoteRequestInit(InitApiRequestEvent $event): void {
    $request = $event->getApiRequest();
    Assert::isInstanceOf($request, RemoteFundingActionInterface::class);
    /** @var \Civi\Funding\Api4\Action\Remote\RemoteFundingActionInterface $request */
    $remoteContactId = $request->getRemoteContactId();
    // GetFieldsAction is called in API explorer, though in that case it's no
    // remote request. Thus, the condition.
    if (!$request instanceof GetFieldsAction || NULL !== $remoteContactId) {
      $this->requestContext->setRemote(TRUE);
    }
    $this->requestContext->setRemoteContactId($remoteContactId);
    if (NULL !== $remoteContactId && '' !== $remoteContactId) {
      $contactId = $this->remoteContactIdResolver->getContactId($remoteContactId);
      $request->setExtraParam('contactId', $contactId);
      $this->requestContext->setResolvedContactId($contactId);
    }
  }

}
