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

use Civi\Api4\FundingApplicationProcess;
use Civi\Funding\ActivityTypeIds;
use Civi\Funding\ApplicationProcess\ApplicationProcessActivityManager;
use Civi\Funding\Entity\ActivityEntity;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessUpdatedEvent;
use Civi\RemoteTools\Api4\OptionsLoaderInterface;
use Civi\RemoteTools\RequestContext\RequestContextInterface;
use CRM_Funding_ExtensionUtil as E;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ApplicationProcessStatusSubscriber implements EventSubscriberInterface {

  private ApplicationProcessActivityManager $activityManager;

  private OptionsLoaderInterface $optionsLoader;

  private RequestContextInterface $requestContext;

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    return [ApplicationProcessUpdatedEvent::class => 'onUpdated'];
  }

  public function __construct(
    ApplicationProcessActivityManager $activityManager,
    OptionsLoaderInterface $optionsLoader,
    RequestContextInterface $requestContext
  ) {
    $this->activityManager = $activityManager;
    $this->optionsLoader = $optionsLoader;
    $this->requestContext = $requestContext;
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function onUpdated(ApplicationProcessUpdatedEvent $event): void {
    if ($event->getPreviousApplicationProcess()->getStatus() !== $event->getApplicationProcess()->getStatus()) {
      $applicationProcess = $event->getApplicationProcess();
      $oldStatus = $event->getPreviousApplicationProcess()->getStatus();
      $activity = ActivityEntity::fromArray([
        'activity_type_id' => ActivityTypeIds::FUNDING_APPLICATION_STATUS_CHANGE,
        'subject' => E::ts('Funding Application Status Changed'),
        'details' => sprintf(
          '<ul><li>%s</li><li>%s</li><li>%s</li></ul>',
          E::ts('Application: %1 (%2)', [
            1 => $applicationProcess->getTitle(),
            2 => $applicationProcess->getIdentifier(),
          ]),
          E::ts('From status: %1', [1 => $this->getStatusLabel($oldStatus)]),
          E::ts('To status: %1', [1 => $this->getStatusLabel($applicationProcess->getStatus())])
        ),
        'funding_application_status_change.from_status' => $oldStatus,
        'funding_application_status_change.to_status' => $applicationProcess->getStatus(),
      ]);

      $this->activityManager->addActivity($this->requestContext->getContactId(), $applicationProcess, $activity);
    }
  }

  /**
   * @throws \CRM_Core_Exception
   */
  private function getStatusLabel(string $status): string {
    return $this->optionsLoader->getOptionLabel(
      FundingApplicationProcess::getEntityName(),
      'status',
      $status
    ) ?? $status;
  }

}
