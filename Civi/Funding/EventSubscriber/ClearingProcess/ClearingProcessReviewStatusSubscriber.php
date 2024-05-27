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
use Civi\Funding\Event\ClearingProcess\ClearingProcessUpdatedEvent;
use Civi\RemoteTools\RequestContext\RequestContextInterface;
use CRM_Funding_ExtensionUtil as E;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ClearingProcessReviewStatusSubscriber implements EventSubscriberInterface {

  private ApplicationProcessActivityManager $activityManager;

  private RequestContextInterface $requestContext;

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    return [ClearingProcessUpdatedEvent::class => 'onUpdated'];
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
  public function onUpdated(ClearingProcessUpdatedEvent $event): void {
    $fromIsReviewContent = $event->getPreviousClearingProcess()->getIsReviewContent();
    $fromIsReviewCalculative = $event->getPreviousClearingProcess()->getIsReviewCalculative();
    $toIsReviewContent = $event->getClearingProcess()->getIsReviewContent();
    $toIsReviewCalculative = $event->getClearingProcess()->getIsReviewCalculative();

    if ($fromIsReviewCalculative !== $toIsReviewCalculative || $fromIsReviewContent !== $toIsReviewContent) {
      $applicationProcess = $event->getApplicationProcess();
      $activity = ActivityEntity::fromArray([
        'activity_type_id:name' => ActivityTypeNames::FUNDING_CLEARING_REVIEW_STATUS_CHANGE,
        'subject' => E::ts('Funding Clearing Review Status Changed'),
        'details' => sprintf(
          '<ul><li>%s</li><li>%s</li><li>%s</li><li>%s</li><li>%s</li></ul>',
          E::ts('Application: %1 (%2)', [
            1 => $applicationProcess->getTitle(),
            2 => $applicationProcess->getIdentifier(),
          ]),
          E::ts('From review content: %1', [1 => $this->getStatusLabel($fromIsReviewContent)]),
          E::ts('To review content: %1', [1 => $this->getStatusLabel($toIsReviewContent)]),
          E::ts('From review calculative: %1', [1 => $this->getStatusLabel($fromIsReviewCalculative)]),
          E::ts('To review calculative: %1', [1 => $this->getStatusLabel($toIsReviewCalculative)]),
        ),
        'funding_clearing_review_status_change.from_is_review_content' => $fromIsReviewContent,
        'funding_clearing_review_status_change.to_is_review_content' => $toIsReviewContent,
        'funding_clearing_review_status_change.from_is_review_calculative' => $fromIsReviewCalculative,
        'funding_clearing_review_status_change.to_is_review_calculative' => $toIsReviewCalculative,
      ]);

      $this->activityManager->addActivity($this->requestContext->getContactId(), $applicationProcess, $activity);
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
