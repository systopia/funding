<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
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

namespace Civi\Funding\Notification\EventSubscriber;

use Civi\Funding\Event\ClearingProcess\ClearingProcessCreatedEvent;
use Civi\Funding\Event\ClearingProcess\ClearingProcessUpdatedEvent;
use Civi\Funding\Notification\NotificationSender;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ClearingProcessNotificationSubscriber implements EventSubscriberInterface {

  private NotificationSender $notificationSender;

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    // Minimum priority so other subscribers may change properties.
    return [
      ClearingProcessCreatedEvent::class => ['onCreated', PHP_INT_MIN],
      ClearingProcessUpdatedEvent::class => ['onUpdated', PHP_INT_MIN],
    ];
  }

  public function __construct(NotificationSender $notificationSender) {
    $this->notificationSender = $notificationSender;
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function onCreated(ClearingProcessCreatedEvent $event): void {
    $this->notificationSender->sendNotification(
      'clearing_process.status_change:' . $event->getClearingProcess()->getStatus(),
      $event->getFundingCase(),
      [
        'fundingApplicationProcess' => $event->getApplicationProcess(),
        'fundingClearingProcess' => $event->getClearingProcess(),
      ]
    );
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function onUpdated(ClearingProcessUpdatedEvent $event): void {
    if ($event->getClearingProcess()->getStatus() !== $event->getPreviousClearingProcess()->getStatus()) {
      $this->notificationSender->sendNotification(
        'clearing_process.status_change:' . $event->getClearingProcess()->getStatus(),
        $event->getFundingCase(),
        [
          'fundingApplicationProcess' => $event->getApplicationProcess(),
          'fundingClearingProcess' => $event->getClearingProcess(),
        ]
      );
    }
  }

}
