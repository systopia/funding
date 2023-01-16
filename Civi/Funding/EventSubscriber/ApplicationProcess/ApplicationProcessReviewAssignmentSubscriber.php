<?php
/*
 * Copyright (C) 2023 SYSTOPIA GmbH
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

use Civi\Funding\ApplicationProcess\ApplicationProcessManager;
use Civi\Funding\Event\ApplicationProcess\ApplicationFormSubmitSuccessEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class ApplicationProcessReviewAssignmentSubscriber implements EventSubscriberInterface {

  private ApplicationProcessManager $applicationProcessManager;

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    return [ApplicationFormSubmitSuccessEvent::class => 'onFormSubmitSuccess'];
  }

  public function __construct(ApplicationProcessManager $applicationProcessManager) {
    $this->applicationProcessManager = $applicationProcessManager;
  }

  public function onFormSubmitSuccess(ApplicationFormSubmitSuccessEvent $event): void {
    if ($this->isReviewStartAction($event->getAction())) {
      $reviewerChanged = FALSE;
      $applicationProcess = $event->getApplicationProcess();
      $permissions = $event->getFundingCase()->getPermissions();

      if (NULL === $applicationProcess->getReviewerCalculativeContactId()
        && $this->hasPermissionReviewCalculative($permissions)) {
        $applicationProcess->setReviewerCalculativeContactId($event->getContactId());
        $reviewerChanged = TRUE;
      }

      if (NULL === $applicationProcess->getReviewerContentContactId()
        && $this->hasPermissionReviewContent($permissions)) {
        $applicationProcess->setReviewerContentContactId($event->getContactId());
        $reviewerChanged = TRUE;
      }

      if ($reviewerChanged) {
        $this->applicationProcessManager->update($event->getContactId(), $event->getApplicationProcessBundle());
      }
    }
  }

  /**
   * @phpstan-param array<string> $permissions
   */
  private function hasPermissionReviewCalculative(array $permissions): bool {
    return in_array('review_calculative', $permissions, TRUE);
  }

  /**
   * @phpstan-param array<string> $permissions
   */
  private function hasPermissionReviewContent(array $permissions): bool {
    return in_array('review_content', $permissions, TRUE);
  }

  private function isReviewStartAction(string $action): bool {
    return 'review' === $action;
  }

}
