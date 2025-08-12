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
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessUpdatedEvent;
use Civi\RemoteTools\RequestContext\RequestContextInterface;
use CRM_Funding_ExtensionUtil as E;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ApplicationSnapshotRestoreSubscriber implements EventSubscriberInterface {

  private ApplicationProcessActivityManager $activityManager;

  private RequestContextInterface $requestContext;

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    // Minimum priority so restore activity is created as last one.
    return [ApplicationProcessUpdatedEvent::class => ['onUpdated', PHP_INT_MIN]];
  }

  public function __construct(
    ApplicationProcessActivityManager $activityManager,
    RequestContextInterface $requestContext
  ) {
    $this->activityManager = $activityManager;
    $this->requestContext = $requestContext;
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function onUpdated(ApplicationProcessUpdatedEvent $event): void {
    $applicationProcess = $event->getApplicationProcess();
    if (NULL !== $applicationProcess->getRestoredSnapshot()) {
      $activity = ActivityEntity::fromArray([
        'activity_type_id' => ActivityTypeIds::FUNDING_APPLICATION_RESTORE,
        'subject' => E::ts('Funding Application Restored'),
        'details' => sprintf(
          E::ts('Application "%1" (%2) restored to version from %3.', [
            1 => $applicationProcess->getTitle(),
            2 => $applicationProcess->getIdentifier(),
            3 => $applicationProcess->getRestoredSnapshot()->getCreationDate()->format(E::ts('Y-m-d H:i:s')),
          ]),
        ),
        'funding_application_restore.application_snapshot_id' => $applicationProcess->getRestoredSnapshot()->getId(),
      ]);

      $this->activityManager->addActivity($this->requestContext->getContactId(), $applicationProcess, $activity);
    }
  }

}
