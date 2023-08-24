<?php
/*
 * Copyright (C) 2023 SYSTOPIA GmbH
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

namespace Civi\Funding\EventSubscriber\ExternalFile;

use Civi\ExternalFile\Event\AuthorizeFileDownloadEvent;
use Civi\Funding\Api4\Permissions;
use Civi\Funding\Contact\FundingRemoteContactIdResolverInterface;
use Civi\RemoteTools\Exception\ResolveContactIdFailedException;
use Civi\RemoteTools\RequestContext\RequestContextInterface;
use CRM_Funding_ExtensionUtil as E;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

final class RemoteAuthorizeExternalFileDownloadInitSubscriber implements EventSubscriberInterface {

  private RequestContextInterface $requestContext;

  private FundingRemoteContactIdResolverInterface $remoteContactIdResolver;

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    return [AuthorizeFileDownloadEvent::class => ['onAuthorize', 1000]];
  }

  public function __construct(
    RequestContextInterface $requestContext,
    FundingRemoteContactIdResolverInterface $remoteContactIdResolver
  ) {
    $this->requestContext = $requestContext;
    $this->remoteContactIdResolver = $remoteContactIdResolver;
  }

  /**
   * @throws \Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException
   */
  public function onAuthorize(AuthorizeFileDownloadEvent $event): void {
    if (E::SHORT_NAME !== $event->getExternalFile()->getExtension()
      || !$event->getRequest()->headers->has('X-Civi-Remote-Contact-Id')) {
      return;
    }

    if (!\CRM_Core_Permission::check([Permissions::ACCESS_REMOTE_FUNDING])) {
      throw new UnauthorizedHttpException('funding-remote', 'Permission to use remote contact ID is missing');
    }

    $this->requestContext->setRemote(TRUE);
    /** @var string $remoteContactId */
    $remoteContactId = $event->getRequest()->headers->get('X-Civi-Remote-Contact-Id');
    try {
      $contactId = $this->remoteContactIdResolver->getContactId($remoteContactId);
      $this->requestContext->setResolvedContactId($contactId);
    }
    catch (ResolveContactIdFailedException $e) {
      throw new UnauthorizedHttpException('funding-remote', 'Unknown remote contact ID', $e);
    }
  }

}
