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

use Civi\Api4\FundingClearingProcess;
use Civi\Funding\ActivityTypeNames;
use Civi\Funding\Api4\OptionsLoaderInterface;
use Civi\Funding\ApplicationProcess\ApplicationProcessActivityManager;
use Civi\Funding\Entity\ActivityEntity;
use Civi\Funding\Event\ClearingProcess\ClearingProcessUpdatedEvent;
use Civi\RemoteTools\RequestContext\RequestContextInterface;
use CRM_Funding_ExtensionUtil as E;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ClearingProcessStatusSubscriber implements EventSubscriberInterface {

  private ApplicationProcessActivityManager $activityManager;

  private OptionsLoaderInterface $optionsLoader;

  private RequestContextInterface $requestContext;

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    return [ClearingProcessUpdatedEvent::class => 'onUpdated'];
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
  public function onUpdated(ClearingProcessUpdatedEvent $event): void {
    $newStatus = $event->getClearingProcess()->getStatus();
    $oldStatus = $event->getPreviousClearingProcess()->getStatus();
    if ($oldStatus !== $newStatus) {
      $applicationProcess = $event->getApplicationProcess();
      $activity = ActivityEntity::fromArray([
        'activity_type_id:name' => ActivityTypeNames::FUNDING_CLEARING_STATUS_CHANGE,
        'subject' => E::ts('Funding Clearing Status Changed'),
        'details' => sprintf(
          '<ul><li>%s</li><li>%s</li><li>%s</li></ul>',
          E::ts('Application: %1 (%2)', [
            1 => $applicationProcess->getTitle(),
            2 => $applicationProcess->getIdentifier(),
          ]),
          E::ts('From status: %1', [1 => $this->getStatusLabel($oldStatus)]),
          E::ts('To status: %1', [1 => $this->getStatusLabel($newStatus)])
        ),
        'funding_clearing_status_change.from_status' => $oldStatus,
        'funding_clearing_status_change.to_status' => $newStatus,
      ]);

      $this->activityManager->addActivity($this->requestContext->getContactId(), $applicationProcess, $activity);
    }
  }

  /**
   * @throws \CRM_Core_Exception
   */
  private function getStatusLabel(string $status): string {
    return $this->optionsLoader->getOptionLabel(
      FundingClearingProcess::getEntityName(),
      'status',
      $status
    ) ?? $status;
  }

}
