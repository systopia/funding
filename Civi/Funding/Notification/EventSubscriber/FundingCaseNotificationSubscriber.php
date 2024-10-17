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

use Civi\Funding\Event\FundingCase\FundingCaseCreatedEvent;
use Civi\Funding\Event\FundingCase\FundingCaseUpdatedEvent;
use Civi\Funding\Notification\NotificationSender;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class FundingCaseNotificationSubscriber implements EventSubscriberInterface {

  private NotificationSender $notificationSender;

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    return [
      FundingCaseCreatedEvent::class => 'onCreated',
      FundingCaseUpdatedEvent::class => 'onUpdated',
    ];
  }

  public function __construct(NotificationSender $notificationSender) {
    $this->notificationSender = $notificationSender;
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function onCreated(FundingCaseCreatedEvent $event): void {
    $this->notificationSender->sendNotification(
      'funding_case.status_change:' . $event->getFundingCase()->getStatus(),
      $event->getFundingCase(),
      []
    );
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function onUpdated(FundingCaseUpdatedEvent $event): void {
    if ($event->getFundingCase()->getStatus() !== $event->getFundingCase()->getStatus()) {
      $this->notificationSender->sendNotification(
        'funding_case.status_change:' . $event->getFundingCase()->getStatus(),
        $event->getFundingCase(),
        []
      );
    }
  }

}
