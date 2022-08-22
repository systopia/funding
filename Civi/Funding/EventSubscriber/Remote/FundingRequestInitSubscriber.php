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

use Civi\Funding\Api4\Action\Remote\RemoteFundingActionInterface;
use Civi\Funding\Contact\FundingRemoteContactIdResolver;
use Civi\Funding\Event\Remote\FundingEvents;
use Civi\RemoteTools\Event\InitApiRequestEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Webmozart\Assert\Assert;

class FundingRequestInitSubscriber implements EventSubscriberInterface {

  private FundingRemoteContactIdResolver $remoteContactIdResolver;

  public function __construct(FundingRemoteContactIdResolver $remoteContactIdResolver) {
    $this->remoteContactIdResolver = $remoteContactIdResolver;
  }

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    return [FundingEvents::REQUEST_INIT_EVENT_NAME => 'onRemoteRequestInit'];
  }

  public function onRemoteRequestInit(InitApiRequestEvent $event): void {
    $session = \CRM_Core_Session::singleton();
    $session->set('isRemote', TRUE, 'funding');
    $request = $event->getApiRequest();
    Assert::isInstanceOf($request, RemoteFundingActionInterface::class);
    /** @var \Civi\Funding\Api4\Action\Remote\RemoteFundingActionInterface $request */
    $remoteContactId = $request->getRemoteContactId();
    if (NULL !== $remoteContactId) {
      $contactId = $this->remoteContactIdResolver->getContactId($remoteContactId);
      $request->setExtraParam('contactId', $contactId);
      $session->set('contactId', $contactId, 'funding');
    }
  }

}
