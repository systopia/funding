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

use Civi\Funding\Event\ApplicationProcess\ApplicationProcessUpdatedEvent;
use Civi\Funding\FundingCase\FundingCaseManager;
use Civi\Funding\FundingCase\FundingCaseStatusDeterminerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ApplicationProcessStatusSubscriber implements EventSubscriberInterface {

  private FundingCaseManager $fundingCaseManager;

  private FundingCaseStatusDeterminerInterface $statusDeterminer;

  public function __construct(
    FundingCaseManager $fundingCaseManager,
    FundingCaseStatusDeterminerInterface $statusDeterminer
  ) {
    $this->fundingCaseManager = $fundingCaseManager;
    $this->statusDeterminer = $statusDeterminer;
  }

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    return [ApplicationProcessUpdatedEvent::class => 'onUpdated'];
  }

  public function onUpdated(ApplicationProcessUpdatedEvent $event): void {
    $fundingCase = $event->getFundingCase();
    if ('closed' === $fundingCase->getStatus()) {
      return;
    }

    if ($event->getPreviousApplicationProcess()->getStatus() === $event->getApplicationProcess()->getStatus()) {
      return;
    }

    if ($this->statusDeterminer->isClosedByApplicationProcess($event->getApplicationProcess()->getStatus())) {
      $fundingCase->setStatus('closed');
      $this->fundingCaseManager->update($fundingCase);
    }
  }

}
