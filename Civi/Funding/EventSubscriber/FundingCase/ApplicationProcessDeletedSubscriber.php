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

use Civi\Funding\ApplicationProcess\ApplicationProcessManager;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessDeletedEvent;
use Civi\Funding\FundingCase\FundingCaseManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class ApplicationProcessDeletedSubscriber implements EventSubscriberInterface {

  private ApplicationProcessManager $applicationProcessManager;

  private FundingCaseManager $fundingCaseManager;

  public function __construct(
    ApplicationProcessManager $applicationProcessManager,
    FundingCaseManager $fundingCaseManager
  ) {
    $this->applicationProcessManager = $applicationProcessManager;
    $this->fundingCaseManager = $fundingCaseManager;
  }

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    return [ApplicationProcessDeletedEvent::class => 'onDeleted'];
  }

  public function onDeleted(ApplicationProcessDeletedEvent $event): void {
    if (!$event->getFundingCaseType()->getIsSummaryApplication() &&
      0 === $this->applicationProcessManager->countByFundingCaseId($event->getFundingCase()->getId())) {
      $this->fundingCaseManager->delete($event->getFundingCase());
    }
  }

}
