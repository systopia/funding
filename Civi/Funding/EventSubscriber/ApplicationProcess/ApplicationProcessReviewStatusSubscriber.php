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
use CRM_Funding_ExtensionUtil as E;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ApplicationProcessReviewStatusSubscriber implements EventSubscriberInterface {

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
    $fromIsReviewContent = $event->getPreviousApplicationProcess()->getIsReviewContent();
    $fromIsReviewCalculative = $event->getPreviousApplicationProcess()->getIsReviewCalculative();

    if ($fromIsReviewCalculative !== $event->getApplicationProcess()->getIsReviewCalculative()
      || $fromIsReviewContent !== $event->getApplicationProcess()->getIsReviewContent()
    ) {
      $applicationProcess = $event->getApplicationProcess();
      $activity = ActivityEntity::fromArray([
        'activity_type_id' => ActivityTypeIds::FUNDING_APPLICATION_REVIEW_STATUS_CHANGE,
        'subject' => E::ts('Funding Application Review Status Changed'),
        'details' => sprintf(
          '<ul><li>%s</li><li>%s</li><li>%s</li><li>%s</li><li>%s</li></ul>',
          E::ts('Application: %1 (%2)', [
            1 => $applicationProcess->getTitle(),
            2 => $applicationProcess->getIdentifier(),
          ]),
          E::ts('From review content: %1', [1 => $this->getStatusLabel($fromIsReviewContent)]),
          E::ts('To review content: %1', [1 => $this->getStatusLabel($applicationProcess->getIsReviewContent())]),
          E::ts('From review calculative: %1', [1 => $this->getStatusLabel($fromIsReviewCalculative)]),
          E::ts('To review calculative: %1',
            [1 => $this->getStatusLabel($applicationProcess->getIsReviewCalculative())]),
        ),
        'funding_application_review_status_change.from_is_review_content' => $fromIsReviewContent,
        'funding_application_review_status_change.to_is_review_content' => $applicationProcess->getIsReviewContent(),
        'funding_application_review_status_change.from_is_review_calculative' => $fromIsReviewCalculative,
        'funding_application_review_status_change.to_is_review_calculative'
        => $applicationProcess->getIsReviewCalculative(),
      ]);

      $this->activityManager->addActivity($event->getContactId(), $applicationProcess, $activity);
    }
  }

  private function getStatusLabel(?bool $status): string {
    if (TRUE === $status) {
      return E::ts('Passed');
    }

    if (FALSE === $status) {
      return E::ts('Failed');
    }

    return E::ts('Undecided');
  }

}
