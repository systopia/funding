<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
 *
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License as published by the Free
 * Software Foundation, either version 3 of the License, or (at your option) any
 * later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types = 1);

namespace Civi\Funding\EventSubscriber\PayoutProcess;

use Civi\Funding\Event\PayoutProcess\DrawdownCreatedEvent;
use Civi\Funding\Event\PayoutProcess\DrawdownDeletedEvent;
use Civi\Funding\Event\PayoutProcess\DrawdownUpdatedEvent;
use Civi\Funding\PayoutProcess\PayoutProcessManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DrawdownSubscriber implements EventSubscriberInterface {

  private PayoutProcessManager $payoutProcessManager;

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    return [
      DrawdownCreatedEvent::class => 'onCreated',
      DrawdownDeletedEvent::class => 'onDeleted',
      DrawdownUpdatedEvent::class => 'onUpdated',
    ];
  }

  public function __construct(PayoutProcessManager $payoutProcessManager) {
    $this->payoutProcessManager = $payoutProcessManager;
  }

  public function onCreated(DrawdownCreatedEvent $event): void {
    $payoutProcess = $event->getPayoutProcess();
    if ($payoutProcess->getModificationDate() != $event->getDrawdown()->getCreationDate()) {
      $payoutProcess->setModificationDate($event->getDrawdown()->getCreationDate());
      $this->payoutProcessManager->update($event->getDrawdownBundle());
    }
  }

  public function onDeleted(DrawdownDeletedEvent $event): void {
    $payoutProcess = $event->getPayoutProcess();
    $payoutProcess->setModificationDate(new \DateTime(\CRM_Utils_Time::date('YmdHis')));
    $this->payoutProcessManager->update($event->getDrawdownBundle());
  }

  public function onUpdated(DrawdownUpdatedEvent $event): void {
    $drawdown = $event->getDrawdown();
    $payoutProcess = $event->getPayoutProcess();

    if (NULL !== $drawdown->getAcceptionDate()
      && $drawdown->getAcceptionDate() != $event->getPreviousDrawdown()->getAcceptionDate()
    ) {
      $newModificationDate = $drawdown->getAcceptionDate();
    }
    else {
      $newModificationDate = new \DateTime(\CRM_Utils_Time::date('YmdHis'));
    }

    if ($payoutProcess->getModificationDate() != $newModificationDate) {
      $payoutProcess->setModificationDate($newModificationDate);
      $this->payoutProcessManager->update($event->getDrawdownBundle());
    }
  }

}
