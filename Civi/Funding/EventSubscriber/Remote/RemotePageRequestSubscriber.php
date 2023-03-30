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

use Civi\Funding\Contact\FundingRemoteContactIdResolverInterface;
use Civi\Funding\Event\Remote\RemotePageRequestEvent;
use Civi\Funding\Session\FundingSessionInterface;
use Civi\RemoteTools\Exception\ResolveContactIdFailedException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class RemotePageRequestSubscriber implements EventSubscriberInterface {

  private FundingRemoteContactIdResolverInterface $remoteContactIdResolver;

  private FundingSessionInterface $session;

  public function __construct(
    FundingRemoteContactIdResolverInterface $remoteContactIdResolver,
    FundingSessionInterface $session
  ) {
    $this->remoteContactIdResolver = $remoteContactIdResolver;
    $this->session = $session;
  }

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    return [RemotePageRequestEvent::class => 'onRemotePageRequest'];
  }

  /**
   * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
   * @throws \Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException
   */
  public function onRemotePageRequest(RemotePageRequestEvent $event): void {
    $request = $event->getRequest();
    $remoteContactId = $request->headers->get('X-Civi-Remote-Contact-Id');
    if (NULL === $remoteContactId) {
      throw new BadRequestHttpException('Remote contact ID missing');
    }

    try {
      $contactId = $this->remoteContactIdResolver->getContactId($remoteContactId);
      $this->session->setResolvedContactId($contactId);
    }
    catch (ResolveContactIdFailedException $e) {
      throw new UnauthorizedHttpException('funding-remote', 'Unknown remote contact ID', $e);
    }
  }

}
