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

namespace Civi\Funding\EventSubscriber\FundingCase;

use Civi\Funding\Entity\ApplicationProcessEntityBundle;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessUpdatedEvent;
use Civi\Funding\FundingCase\FundingCaseManager;
use Civi\Funding\FundingCase\StatusDeterminer\FundingCaseStatusDeterminerInterface;
use Civi\Funding\FundingCaseTypeServiceLocatorContainer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ApplicationProcessStatusSubscriber implements EventSubscriberInterface {

  private FundingCaseManager $fundingCaseManager;

  private FundingCaseTypeServiceLocatorContainer $serviceLocatorContainer;

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    return [ApplicationProcessUpdatedEvent::class => 'onUpdated'];
  }

  public function __construct(
    FundingCaseManager $fundingCaseManager,
    FundingCaseTypeServiceLocatorContainer $serviceLocatorContainer
  ) {
    $this->fundingCaseManager = $fundingCaseManager;
    $this->serviceLocatorContainer = $serviceLocatorContainer;
  }

  public function onUpdated(ApplicationProcessUpdatedEvent $event): void {
    $fundingCase = $event->getFundingCase();

    $previousStatus = $event->getPreviousApplicationProcess()->getStatus();
    if ($previousStatus === $event->getApplicationProcess()->getStatus()) {
      return;
    }

    $applicationProcessBundle = $event->getApplicationProcessBundle();
    $newStatus = $this->getStatusDeterminer($applicationProcessBundle)
      ->getStatusOnApplicationProcessStatusChange($applicationProcessBundle, $event->getPreviousApplicationProcess());
    if ($fundingCase->getStatus() !== $newStatus) {
      $fundingCase->setStatus($newStatus);
      $this->fundingCaseManager->update($fundingCase);
    }
  }

  private function getStatusDeterminer(
    ApplicationProcessEntityBundle $applicationProcessBundle
  ): FundingCaseStatusDeterminerInterface {
    return $this->serviceLocatorContainer
      ->get($applicationProcessBundle->getFundingCaseType()->getName())
      ->getFundingCaseStatusDeterminer();
  }

}
