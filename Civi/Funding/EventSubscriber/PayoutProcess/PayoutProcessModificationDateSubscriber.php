<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
 *
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License as published by the Free
 * Software Foundation, either version 3 of the License, or (at your option) any
 * later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types = 1);

namespace Civi\Funding\EventSubscriber\PayoutProcess;

use Civi\Funding\Event\PayoutProcess\PayoutProcessUpdatedEvent;
use Civi\Funding\FundingCase\FundingCaseManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PayoutProcessModificationDateSubscriber implements EventSubscriberInterface {

  private FundingCaseManager $fundingCaseManager;

  public function __construct(FundingCaseManager $fundingCaseManager) {
    $this->fundingCaseManager = $fundingCaseManager;
  }

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    return [PayoutProcessUpdatedEvent::class => 'onUpdated'];
  }

  public function onUpdated(PayoutProcessUpdatedEvent $event): void {
    $fundingCase = $event->getFundingCase();
    if ($fundingCase->getModificationDate() != $event->getPayoutProcess()->getModificationDate()) {
      $fundingCase->setModificationDate($event->getPayoutProcess()->getModificationDate());
      $this->fundingCaseManager->update($fundingCase);
    }
  }

}
