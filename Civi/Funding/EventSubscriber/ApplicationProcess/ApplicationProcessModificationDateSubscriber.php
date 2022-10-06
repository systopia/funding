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

use Civi\Funding\Event\ApplicationProcess\ApplicationProcessUpdatedEvent;
use Civi\Funding\FundingCase\FundingCaseManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ApplicationProcessModificationDateSubscriber implements EventSubscriberInterface {

  private FundingCaseManager $fundingCaseManager;

  public function __construct(FundingCaseManager $fundingCaseManager) {
    $this->fundingCaseManager = $fundingCaseManager;
  }

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    return [ApplicationProcessUpdatedEvent::class => 'onUpdated'];
  }

  public function onUpdated(ApplicationProcessUpdatedEvent $event): void {
    $fundingCase = $event->getFundingCase();
    $fundingCase->setModificationDate($event->getApplicationProcess()->getModificationDate());
    $this->fundingCaseManager->update($fundingCase);
  }

}
