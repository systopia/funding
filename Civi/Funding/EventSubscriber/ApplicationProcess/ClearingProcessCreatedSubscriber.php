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
use Civi\Funding\Event\ClearingProcess\ClearingProcessCreatedEvent;
use Civi\Funding\FundingCaseTypeServiceLocatorContainer;
use Civi\RemoteTools\RequestContext\RequestContextInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ClearingProcessCreatedSubscriber implements EventSubscriberInterface {

  private ApplicationProcessManager $applicationProcessManager;

  private RequestContextInterface $requestContext;

  private FundingCaseTypeServiceLocatorContainer $serviceLocatorContainer;

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    return [ClearingProcessCreatedEvent::class => 'onCreated'];
  }

  public function __construct(
    ApplicationProcessManager $applicationProcessManager,
    RequestContextInterface $requestContext,
    FundingCaseTypeServiceLocatorContainer $serviceLocatorContainer
  ) {
    $this->applicationProcessManager = $applicationProcessManager;
    $this->requestContext = $requestContext;
    $this->serviceLocatorContainer = $serviceLocatorContainer;
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function onCreated(ClearingProcessCreatedEvent $event): void {
    $applicationProcess = $event->getApplicationProcess();
    $event->getApplicationProcess()->setFullStatus(
      $this->getStatusDeterminer($event->getFundingCaseType())
        ->getStatusOnClearingProcessCreated($applicationProcess->getFullStatus())
    );
    $this->applicationProcessManager->update(
      $this->requestContext->getContactId(), $event->getApplicationProcessBundle()
    );
  }

  private function getStatusDeterminer(
    FundingCaseTypeEntity $fundingCaseType
  ): ApplicationProcessStatusDeterminerInterface {
    return $this->serviceLocatorContainer->get($fundingCaseType->getName())->getApplicationProcessStatusDeterminer();
  }

}
