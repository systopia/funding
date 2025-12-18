<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
 *
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License as published by the Free
 * Software Foundation, either version 3 of the License, or (at your option) any
 * later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types = 1);

namespace Civi\Funding\EventSubscriber\ClearingProcess;

use Civi\Funding\ActivityTypeNames;
use Civi\Funding\ApplicationProcess\ApplicationProcessActivityManager;
use Civi\Funding\ClearingProcess\ClearingProcessManager;
use Civi\Funding\Entity\ApplicationProcessEntity;
use Civi\Funding\Entity\ClearingProcessEntityBundle;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessUpdatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Webmozart\Assert\Assert;

/**
 * If an application process is reopened the status of the corresponding
 * clearing process gets set to the previous status if it was rejected.
 */
class ApplicationProcessReopenSubscriber implements EventSubscriberInterface {

  private ApplicationProcessActivityManager $activityManager;

  private ClearingProcessManager $clearingProcessManager;

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    return [ApplicationProcessUpdatedEvent::class => 'onUpdated'];
  }

  public function __construct(
    ApplicationProcessActivityManager $activityManager,
    ClearingProcessManager $clearingProcessManager
  ) {
    $this->activityManager = $activityManager;
    $this->clearingProcessManager = $clearingProcessManager;
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function onUpdated(ApplicationProcessUpdatedEvent $event): void {
    $applicationProcess = $event->getApplicationProcess();
    $previousApplicationProcess = $event->getPreviousApplicationProcess();
    if (!$this->isApplicationProcessReopened($applicationProcess, $previousApplicationProcess)) {
      return;
    }

    $clearingProcess = $this->clearingProcessManager->getByApplicationProcessId(
      $event->getApplicationProcess()->getId()
    );
    if (NULL === $clearingProcess || 'rejected' !== $clearingProcess->getStatus()) {
      return;
    }

    $activity = $this->activityManager->getLastByApplicationProcessAndType(
      $applicationProcess->getId(),
      ActivityTypeNames::FUNDING_CLEARING_STATUS_CHANGE
    );
    if (NULL === $activity) {
      return;
    }

    $previousStatus = $activity->get('funding_clearing_status_change.from_status');
    Assert::stringNotEmpty($previousStatus);

    // Amounts admitted will be reset when clearing process leaves status "rejected".
    if ('accepted' === $previousStatus) {
      $clearingProcess->setStatus('review');
    }
    else {
      $clearingProcess->setStatus($previousStatus);
    }

    if (TRUE === $clearingProcess->getIsReviewCalculative()) {
      $clearingProcess->setIsReviewCalculative(NULL);
    }

    $clearingProcess->setModificationDate($applicationProcess->getModificationDate());
    $this->clearingProcessManager->update(new ClearingProcessEntityBundle(
      $clearingProcess,
      $event->getApplicationProcessBundle()
    ));
  }

  private function isApplicationProcessReopened(
    ApplicationProcessEntity $applicationProcess,
    ApplicationProcessEntity $previousApplicationProcess
  ): bool {
    return ($previousApplicationProcess->getIsWithdrawn() || $previousApplicationProcess->getIsRejected()) &&
      !$applicationProcess->getIsWithdrawn() && !$applicationProcess->getIsRejected();
  }

}
