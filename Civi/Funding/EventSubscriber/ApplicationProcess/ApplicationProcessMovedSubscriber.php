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

use Civi\Funding\ActivityTypeNames;
use Civi\Funding\ApplicationProcess\ApplicationProcessActivityManager;
use Civi\Funding\Entity\ActivityEntity;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessUpdatedEvent;
use CRM_Funding_ExtensionUtil as E;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ApplicationProcessMovedSubscriber implements EventSubscriberInterface {

  private ApplicationProcessActivityManager $activityManager;

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    return [ApplicationProcessUpdatedEvent::class => 'onUpdated'];
  }

  public function __construct(ApplicationProcessActivityManager $activityManager) {
    $this->activityManager = $activityManager;
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function onUpdated(ApplicationProcessUpdatedEvent $event): void {
    if ($event->getPreviousFundingCase()->getId() !== $event->getFundingCase()->getId()) {
      $applicationProcess = $event->getApplicationProcess();
      $activity = ActivityEntity::fromArray([
        'activity_type_id:name' => ActivityTypeNames::FUNDING_APPLICATION_MOVE,
        'subject' => E::ts('Funding Application Moved'),
        'details' => sprintf(
          '<ul><li>%s</li><li>%s</li><li>%s</li><li>%s</li></ul>',
          E::ts('Application: %1 (%2)', [
            1 => $applicationProcess->getTitle(),
            2 => $applicationProcess->getIdentifier(),
          ]),
          E::ts('Previous identifier: %1', [1 => $event->getPreviousApplicationProcess()->getIdentifier()]),
          E::ts('From funding case: %1', [1 => $event->getPreviousFundingCase()->getIdentifier()]),
          E::ts('To funding case: %1', [1 => $event->getFundingCase()->getIdentifier()]),
        ),
        'funding_application_move.previous_application_process_identifier'
        => $event->getPreviousApplicationProcess()->getIdentifier(),
        'funding_application_move.from_funding_case_identifier' => $event->getPreviousFundingCase()->getIdentifier(),
        'funding_application_move.to_funding_case_identifier' => $event->getFundingCase()->getIdentifier(),
      ]);
      $this->activityManager->addActivity($applicationProcess, $activity);
    }
  }

}
