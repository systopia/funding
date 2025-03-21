<?php
/*
 * Copyright (C) 2024 SYSTOPIA GmbH
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

use Civi\Funding\Event\ApplicationProcess\ApplicationProcessCreatedEvent;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessUpdatedEvent;
use Civi\Funding\Notification\NotificationSender;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ApplicationProcessNotificationSubscriber implements EventSubscriberInterface {

  private NotificationSender $notificationSender;

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    return [
      ApplicationProcessCreatedEvent::class => 'onCreated',
      ApplicationProcessUpdatedEvent::class => 'onUpdated',
    ];
  }

  public function __construct(NotificationSender $notificationSender) {
    $this->notificationSender = $notificationSender;
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function onCreated(ApplicationProcessCreatedEvent $event): void {
    $this->notificationSender->sendNotification(
      'application_process.status_change:' . $event->getApplicationProcess()->getStatus(),
      $event->getFundingCase(),
      [
        'fundingApplicationProcess' => $event->getApplicationProcess(),
      ]
    );
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function onUpdated(ApplicationProcessUpdatedEvent $event): void {
    if ($event->getApplicationProcess()->getStatus() !== $event->getPreviousApplicationProcess()->getStatus()) {
      $this->notificationSender->sendNotification(
        'application_process.status_change:' . $event->getApplicationProcess()->getStatus(),
        $event->getFundingCase(),
        [
          'fundingApplicationProcess' => $event->getApplicationProcess(),
        ]
      );
    }
  }

}
