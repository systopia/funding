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

use Civi\Funding\ActivityTypeIds;
use Civi\Funding\ApplicationProcess\ApplicationProcessActivityManager;
use Civi\Funding\Entity\ActivityEntity;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessCreatedEvent;
use CRM_Funding_ExtensionUtil as E;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class ApplicationProcessCreatedSubscriber implements EventSubscriberInterface {

  private ApplicationProcessActivityManager $activityManager;

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    return [ApplicationProcessCreatedEvent::class => 'onCreated'];
  }

  public function __construct(ApplicationProcessActivityManager $activityManager) {
    $this->activityManager = $activityManager;
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function onCreated(ApplicationProcessCreatedEvent $event): void {
    $applicationProcess = $event->getApplicationProcess();
    $activity = ActivityEntity::fromArray([
      'activity_type_id' => ActivityTypeIds::FUNDING_APPLICATION_CREATE,
      'subject' => E::ts('Funding application process created'),
      'details' => E::ts('Application process: %1 (%2)',
        [
          1 => $applicationProcess->getTitle(),
          2 => $applicationProcess->getIdentifier(),
        ]
      ),
    ]);
    $this->activityManager->addActivity($event->getContactId(), $applicationProcess, $activity);
  }

}
