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

namespace Civi\Funding\SammelantragKurs\EventSubscriber;

use Civi\Funding\ApplicationProcess\ApplicationProcessManager;
use Civi\Funding\ApplicationProcess\ApplicationSnapshotManager;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessPreUpdateEvent;
use Civi\Funding\SammelantragKurs\KursConstants;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Applications in this funding case type can be modified without a new review
 * under these conditions:
 *   - The title is not changed.
 *   - The short description is not changed.
 *   - The amount requested is not increased, or the new sum of the amounts
 *     requested of all (possibly) eligible applications in the same funding
 *     case doesn't exceed the amount approved.
 *
 * This subscriber ensures that under those conditions the status is changed to
 * "eligible" instead of "rework-review-requested".
 */
class KursApplicationStatusSubscriber implements EventSubscriberInterface {

  private ApplicationProcessManager $applicationProcessManager;

  private ApplicationSnapshotManager $snapshotManager;

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    return [ApplicationProcessPreUpdateEvent::class => 'onPreUpdate'];
  }

  public function __construct(
    ApplicationProcessManager $applicationProcessManager,
    ApplicationSnapshotManager $snapshotManager
  ) {
    $this->applicationProcessManager = $applicationProcessManager;
    $this->snapshotManager = $snapshotManager;
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function onPreUpdate(ApplicationProcessPreUpdateEvent $event): void {
    if ($event->getFundingCaseType()->getName() !== KursConstants::FUNDING_CASE_TYPE_NAME) {
      return;
    }

    $applicationProcess = $event->getApplicationProcess();
    if ($applicationProcess->getStatus() === 'rework-review-requested'
      && $applicationProcess->getStatus() !== $event->getPreviousApplicationProcess()->getStatus()
    ) {
      if (!$this->isReviewRequired($event)) {
        $applicationProcess->setStatus('eligible');
      }
    }
  }

  /**
   * @throws \CRM_Core_Exception
   */
  private function isReviewRequired(ApplicationProcessPreUpdateEvent $event): bool {
    $applicationProcess = $event->getApplicationProcess();
    $snapshot = $this->snapshotManager->getLastByApplicationProcessId($applicationProcess->getId());
    if (NULL === $snapshot) {
      // Should not happen.
      return TRUE;
    }

    if ($applicationProcess->getTitle() !== $snapshot->getTitle()
      || $applicationProcess->getShortDescription() !== $snapshot->getShortDescription()) {
      return TRUE;
    }

    if ($applicationProcess->getAmountRequested() <= $snapshot->getAmountRequested()) {
      return FALSE;
    }

    return $this->getSumAmountRequested($event) > ($event->getFundingCase()->getAmountApproved() ?? 0);
  }

  /**
   * @throws \CRM_Core_Exception
   */
  private function getSumAmountRequested(ApplicationProcessPreUpdateEvent $event): float {
    $updatedApplicationProcessId = $event->getApplicationProcess()->getId();
    $sumAmountRequested = $event->getApplicationProcess()->getAmountRequested();

    $fundingCaseId = $event->getFundingCase()->getId();
    foreach ($this->applicationProcessManager->getByFundingCaseId($fundingCaseId) as $applicationProcess) {
      if ($applicationProcess->getId() !== $updatedApplicationProcessId
        && FALSE !== $applicationProcess->getIsEligible()
      ) {
        $sumAmountRequested += $applicationProcess->getAmountRequested();
      }
    }

    return $sumAmountRequested;
  }

}
