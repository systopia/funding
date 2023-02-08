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

final class ApplicationProcessReworkTaskSubscriber implements EventSubscriberInterface {

  private ApplicationProcessActionStatusInfoContainer $infoContainer;

  private ApplicationProcessTaskManager $taskManager;

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    return [
      ApplicationFormSubmitSuccessEvent::class => 'onFormSubmitSuccess',
      ApplicationProcessUpdatedEvent::class => 'onUpdated',
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
   * @throws \API_Exception
   */
  public function onFormSubmitSuccess(ApplicationFormSubmitSuccessEvent $event): void {
    $applicationProcess = $event->getApplicationProcess();

    if ($this->getInfo($event->getFundingCaseType())->isApplyAction($event->getAction())) {
      $this->taskManager->completeExternalTask(
        $applicationProcess->getId(),
        TaskType::REWORK,
      );
    }
    elseif (!$this->getInfo($event->getFundingCaseType())->isChangeRequiredStatus($applicationProcess->getStatus())) {
      $this->taskManager->cancelExternalTask(
        $applicationProcess->getId(),
        TaskType::REWORK,
      );
    }
  }

  /**
   * @throws \API_Exception
   */
  public function onUpdated(ApplicationProcessUpdatedEvent $event): void {
    $applicationProcess = $event->getApplicationProcess();
    $previousApplicationProcess = $event->getPreviousApplicationProcess();

    if ($applicationProcess->getStatus() !== $previousApplicationProcess->getStatus()) {
      if ($this->getInfo($event->getFundingCaseType())->isChangeRequiredStatus($applicationProcess->getStatus())) {
        $this->taskManager->addExternalTask(
          $event->getContactId(),
          $applicationProcess,
          TaskType::REWORK,
          E::ts('Rework funding application'),
        );
      }
    }
  }

  private function getInfo(FundingCaseTypeEntity $fundingCaseType): ApplicationProcessActionStatusInfoInterface {
    return $this->infoContainer->get($fundingCaseType->getName());
  }

}
