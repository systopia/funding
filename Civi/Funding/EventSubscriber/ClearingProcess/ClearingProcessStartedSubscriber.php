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

use Civi\Funding\ActivityTypeNames;
use Civi\Funding\ApplicationProcess\ApplicationProcessActivityManager;
use Civi\Funding\Entity\ActivityEntity;
use Civi\Funding\Event\ClearingProcess\ClearingProcessStartedEvent;
use Civi\RemoteTools\RequestContext\RequestContextInterface;
use CRM_Funding_ExtensionUtil as E;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ClearingProcessStartedSubscriber implements EventSubscriberInterface {

  private ApplicationProcessActivityManager $activityManager;

  private RequestContextInterface $requestContext;

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    return [ClearingProcessStartedEvent::class => 'onStarted'];
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
  public function onStarted(ClearingProcessStartedEvent $event): void {
    $applicationProcess = $event->getApplicationProcess();
    $activity = ActivityEntity::fromArray([
      'activity_type_id:name' => ActivityTypeNames::FUNDING_CLEARING_CREATE,
      'subject' => E::ts('Funding Clearing Started'),
      'details' => E::ts('Application: %1 (%2)',
        [
          1 => $applicationProcess->getTitle(),
          2 => $applicationProcess->getIdentifier(),
        ]
      ),
    ]);
    $this->activityManager->addActivity($this->requestContext->getContactId(), $applicationProcess, $activity);
  }

}
