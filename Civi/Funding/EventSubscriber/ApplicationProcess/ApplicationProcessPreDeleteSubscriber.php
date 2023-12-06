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

namespace Civi\Funding\EventSubscriber\ApplicationProcess;

use Civi\Funding\ApplicationProcess\ApplicationExternalFileManagerInterface;
use Civi\Funding\ApplicationProcess\ApplicationProcessActivityManager;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessPreDeleteEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ApplicationProcessPreDeleteSubscriber implements EventSubscriberInterface {

  private ApplicationProcessActivityManager $activityManager;

  private ApplicationExternalFileManagerInterface $externalFileManager;

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    return [ApplicationProcessPreDeleteEvent::class => 'onPreDelete'];
  }

  public function __construct(
    ApplicationProcessActivityManager $activityManager,
    ApplicationExternalFileManagerInterface $externalFileManager
  ) {
    $this->activityManager = $activityManager;
    $this->externalFileManager = $externalFileManager;
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function onPreDelete(ApplicationProcessPreDeleteEvent $event): void {
    $this->activityManager->deleteByApplicationProcess($event->getApplicationProcess()->getId());
    $this->externalFileManager->deleteFiles($event->getApplicationProcess()->getId(), []);
  }

}
