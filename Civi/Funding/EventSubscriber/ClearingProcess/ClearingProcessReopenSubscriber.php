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

namespace Civi\Funding\EventSubscriber\ClearingProcess;

use Civi\Funding\ClearingProcess\ClearingCostItemManager;
use Civi\Funding\ClearingProcess\ClearingResourcesItemManager;
use Civi\Funding\Event\ClearingProcess\ClearingProcessUpdatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Resets the admitted amounts of clearing items when clearing process is
 * reopened.
 */
class ClearingProcessReopenSubscriber implements EventSubscriberInterface {

  private ClearingCostItemManager $clearingCostItemManager;

  private ClearingResourcesItemManager $clearingResourcesItemManager;

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    return [ClearingProcessUpdatedEvent::class => 'onUpdated'];
  }

  public function __construct(
    ClearingCostItemManager $clearingCostItemManager,
    ClearingResourcesItemManager $clearingResourcesItemManager
  ) {
    $this->clearingCostItemManager = $clearingCostItemManager;
    $this->clearingResourcesItemManager = $clearingResourcesItemManager;
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function onUpdated(ClearingProcessUpdatedEvent $event): void {
    $newStatus = $event->getClearingProcess()->getStatus();
    $oldStatus = $event->getPreviousClearingProcess()->getStatus();
    if ('rejected' === $oldStatus && 'rejected' !== $newStatus) {
      $clearingProcessId = $event->getClearingProcess()->getId();
      $this->clearingCostItemManager->resetAmountsAdmittedByClearingProcessId($clearingProcessId);
      $this->clearingResourcesItemManager->resetAmountsAdmittedByClearingProcessId($clearingProcessId);
    }
  }

}
