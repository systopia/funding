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

use Civi\Funding\ApplicationProcess\ActionStatusInfo\ApplicationProcessActionStatusInfoContainer;
use Civi\Funding\ApplicationProcess\ActionStatusInfo\ApplicationProcessActionStatusInfoInterface;
use Civi\Funding\Entity\ApplicationProcessEntityBundle;
use Civi\Funding\Entity\FundingCaseTypeEntity;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessPreCreateEvent;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessPreUpdateEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class ApplicationProcessEligibleSubscriber implements EventSubscriberInterface {

  private ApplicationProcessActionStatusInfoContainer $infoContainer;

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    return [
      ApplicationProcessPreCreateEvent::class => 'onPreCreate',
      ApplicationProcessPreUpdateEvent::class => 'onPreUpdate',
    ];
  }

  public function __construct(ApplicationProcessActionStatusInfoContainer $infoContainer) {
    $this->infoContainer = $infoContainer;
  }

  public function onPreCreate(ApplicationProcessPreCreateEvent $event): void {
    $this->updateIsEligibleFlag($event->getApplicationProcessBundle());
  }

  public function onPreUpdate(ApplicationProcessPreUpdateEvent $event): void {
    $this->updateIsEligibleFlag($event->getApplicationProcessBundle());
  }

  private function getInfo(FundingCaseTypeEntity $fundingCaseType): ApplicationProcessActionStatusInfoInterface {
    return $this->infoContainer->get($fundingCaseType->getName());
  }

  private function updateIsEligibleFlag(ApplicationProcessEntityBundle $applicationProcessBundle): void {
    $applicationProcessBundle->getApplicationProcess()->setIsEligible(
      $this->getInfo($applicationProcessBundle->getFundingCaseType())->isEligibleStatus(
        $applicationProcessBundle->getApplicationProcess()->getStatus()
      )
    );
  }

}
