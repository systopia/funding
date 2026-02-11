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

namespace Civi\Funding\EventSubscriber\ApplicationProcess;

use Civi\Funding\ApplicationProcess\ApplicationProcessManager;
use Civi\Funding\ApplicationProcess\StatusDeterminer\ApplicationProcessStatusDeterminerInterface;
use Civi\Funding\Entity\FundingCaseTypeEntity;
use Civi\Funding\Event\ClearingProcess\ClearingProcessStartedEvent;
use Civi\Funding\FundingCaseTypeServiceLocatorContainer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ClearingProcessStartedSubscriber implements EventSubscriberInterface {

  private ApplicationProcessManager $applicationProcessManager;

  private FundingCaseTypeServiceLocatorContainer $serviceLocatorContainer;

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    return [ClearingProcessStartedEvent::class => 'onStarted'];
  }

  public function __construct(
    ApplicationProcessManager $applicationProcessManager,
    FundingCaseTypeServiceLocatorContainer $serviceLocatorContainer
  ) {
    $this->applicationProcessManager = $applicationProcessManager;
    $this->serviceLocatorContainer = $serviceLocatorContainer;
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function onStarted(ClearingProcessStartedEvent $event): void {
    $applicationProcess = $event->getApplicationProcess();
    $newFullStatus = $this->getStatusDeterminer($event->getFundingCaseType())
      ->getStatusOnClearingProcessStarted($applicationProcess->getFullStatus());
    // @phpstan-ignore notEqual.notAllowed
    if ($applicationProcess->getFullStatus() != $newFullStatus) {
      $event->getApplicationProcess()->setFullStatus($newFullStatus);
      $this->applicationProcessManager->update($event->getClearingProcessBundle());
    }
  }

  private function getStatusDeterminer(
    FundingCaseTypeEntity $fundingCaseType
  ): ApplicationProcessStatusDeterminerInterface {
    return $this->serviceLocatorContainer->get($fundingCaseType->getName())->getApplicationProcessStatusDeterminer();
  }

}
