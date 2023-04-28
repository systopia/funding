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

use Civi\Funding\ApplicationProcess\ActionStatusInfo\ApplicationProcessActionStatusInfoContainer;
use Civi\Funding\ApplicationProcess\ActionStatusInfo\ApplicationProcessActionStatusInfoInterface;
use Civi\Funding\ApplicationProcess\ApplicationProcessTaskManager;
use Civi\Funding\ApplicationProcess\TaskType;
use Civi\Funding\Entity\FundingCaseTypeEntity;
use Civi\Funding\Event\ApplicationProcess\ApplicationFormSubmitSuccessEvent;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessUpdatedEvent;
use CRM_Funding_ExtensionUtil as E;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class ApplicationProcessReviewTaskSubscriber implements EventSubscriberInterface {

  private ApplicationProcessActionStatusInfoContainer $infoContainer;

  private ApplicationProcessTaskManager $taskManager;

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    return [
      ApplicationFormSubmitSuccessEvent::class => ['onFormSubmitSuccess', -10],
      ApplicationProcessUpdatedEvent::class => ['onUpdated'],
    ];
  }

  public function __construct(
    ApplicationProcessActionStatusInfoContainer $infoContainer,
    ApplicationProcessTaskManager $taskManager
  ) {
    $this->infoContainer = $infoContainer;
    $this->taskManager = $taskManager;
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function onFormSubmitSuccess(ApplicationFormSubmitSuccessEvent $event): void {
    $applicationProcess = $event->getApplicationProcess();

    if ($this->getInfo($event->getFundingCaseType())->isReviewStartAction($event->getAction())) {
      $this->taskManager->addOrAssignInternalTask(
        $event->getContactId(),
        $applicationProcess,
        $applicationProcess->getReviewerCalculativeContactId(),
        TaskType::REVIEW_CALCULATIVE,
        E::ts('Review Funding Application (calculative)'),
      );

      $this->taskManager->addOrAssignInternalTask(
        $event->getContactId(),
        $applicationProcess,
        $applicationProcess->getReviewerContentContactId(),
        TaskType::REVIEW_CONTENT,
        E::ts('Review Funding Application (content)'),
      );
    }
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function onUpdated(ApplicationProcessUpdatedEvent $event): void {
    $applicationProcess = $event->getApplicationProcess();
    $previousApplicationProcess = $event->getPreviousApplicationProcess();

    if ($applicationProcess->getIsReviewCalculative() !== $previousApplicationProcess->getIsReviewCalculative()) {
      if (NULL !== $applicationProcess->getIsReviewCalculative()) {
        $this->taskManager->completeInternalTask($applicationProcess->getId(), TaskType::REVIEW_CALCULATIVE);
      }
    }

    if ($applicationProcess->getIsReviewContent() !== $previousApplicationProcess->getIsReviewCalculative()) {
      if (NULL !== $applicationProcess->getIsReviewContent()) {
        $this->taskManager->completeInternalTask($applicationProcess->getId(), TaskType::REVIEW_CONTENT);
      }
    }

    if ($applicationProcess->getStatus() !== $previousApplicationProcess->getStatus()) {
      if (!$this->getInfo($event->getFundingCaseType())->isReviewStatus($applicationProcess->getStatus())) {
        $this->taskManager->cancelInternalTask($applicationProcess->getId(), TaskType::REVIEW_CALCULATIVE);
        $this->taskManager->cancelInternalTask($applicationProcess->getId(), TaskType::REVIEW_CONTENT);
      }
    }

    if ($applicationProcess->getReviewerCalculativeContactId() !==
      $previousApplicationProcess->getReviewerCalculativeContactId()) {
      $this->taskManager->assignInternalTask(
        $applicationProcess->getId(),
        $applicationProcess->getReviewerCalculativeContactId(),
        TaskType::REVIEW_CALCULATIVE,
      );
    }

    if ($applicationProcess->getReviewerContentContactId() !==
      $previousApplicationProcess->getReviewerContentContactId()) {
      $this->taskManager->assignInternalTask(
        $applicationProcess->getId(),
        $applicationProcess->getReviewerContentContactId(),
        TaskType::REVIEW_CONTENT,
      );
    }
  }

  private function getInfo(FundingCaseTypeEntity $fundingCaseType): ApplicationProcessActionStatusInfoInterface {
    return $this->infoContainer->get($fundingCaseType->getName());
  }

}
