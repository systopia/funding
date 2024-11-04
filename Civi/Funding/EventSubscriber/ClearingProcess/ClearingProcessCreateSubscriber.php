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

use Civi\Funding\ClearingProcess\ClearingProcessManager;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessCreatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ClearingProcessCreateSubscriber implements EventSubscriberInterface {

  private ClearingProcessManager $clearingProcessManager;

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    return [ApplicationProcessCreatedEvent::class => 'onCreated'];
  }

  public function __construct(ClearingProcessManager $clearingProcessManager) {
    $this->clearingProcessManager = $clearingProcessManager;
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function onCreated(ApplicationProcessCreatedEvent $event): void {
    $this->clearingProcessManager->create($event->getApplicationProcessBundle());
  }

}
