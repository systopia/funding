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

use Civi\Funding\Event\PayoutProcess\DrawdownCreatedEvent;
use Civi\Funding\Event\PayoutProcess\DrawdownUpdatedEvent;
use Civi\Funding\Notification\NotificationSender;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DrawdownNotificationSubscriber implements EventSubscriberInterface {

  private NotificationSender $notificationSender;

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    // Minimum priority so other subscribers may change properties.
    return [
      DrawdownCreatedEvent::class => ['onCreated', PHP_INT_MIN],
      DrawdownUpdatedEvent::class => ['onUpdated', PHP_INT_MIN],
    ];
  }

  public function __construct(NotificationSender $notificationSender) {
    $this->notificationSender = $notificationSender;
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function onCreated(DrawdownCreatedEvent $event): void {
    $this->notificationSender->sendNotification(
      'drawdown.status_change:' . $event->getDrawdown()->getStatus(),
      $event->getFundingCase(),
      [
        'fundingDrawdown' => $event->getDrawdown(),
        'fundingPayoutProcess' => $event->getPayoutProcess(),
      ]
    );
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function onUpdated(DrawdownUpdatedEvent $event): void {
    if ($event->getDrawdown()->getStatus() !== $event->getPreviousDrawdown()->getStatus()) {
      $this->notificationSender->sendNotification(
        'drawdown.status_change:' . $event->getDrawdown()->getStatus(),
        $event->getFundingCase(),
        [
          'fundingDrawdown' => $event->getDrawdown(),
          'fundingPayoutProcess' => $event->getPayoutProcess(),
        ]
      );
    }
  }

}
