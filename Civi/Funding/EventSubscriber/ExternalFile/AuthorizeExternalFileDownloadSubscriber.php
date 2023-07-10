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
use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\Api4\Query\Comparison;
use CRM_Funding_ExtensionUtil as E;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class AuthorizeExternalFileDownloadSubscriber implements EventSubscriberInterface {

  private Api4Interface $api4;

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    return [AuthorizeFileDownloadEvent::class => 'onAuthorize'];
  }

  public function __construct(Api4Interface $api4) {
    $this->api4 = $api4;
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function onAuthorize(AuthorizeFileDownloadEvent $event): void {
    if (E::SHORT_NAME !== $event->getExternalFile()->getExtension()) {
      return;
    }

    $customData = $event->getExternalFile()->getCustomData();
    $entityName = $customData['entityName'] ?? NULL;
    $entityId = $customData['entityId'] ?? NULL;
    if (is_string($entityName) && is_int($entityId)) {
      $event->setAuthorized($this->hasAccess($entityName, $entityId));
    }
  }

  /**
   * @throws \CRM_Core_Exception
   */
  private function hasAccess(string $entityName, int $entityId): bool {
    return 1 === $this->api4->countEntities(
      $entityName,
      Comparison::new('id', '=', $entityId),
      ['checkPermissions' => FALSE]
    );
  }

}
